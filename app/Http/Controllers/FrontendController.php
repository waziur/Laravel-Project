<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Service;
use App\Services\BookingSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FrontendController extends Controller
{
    public function home(): View
    {
        return view('pages.site.index', $this->pageData('Home', 'home'));
    }

    public function about(): View
    {
        return view('pages.site.about', $this->pageData('About Us', 'about'));
    }

    public function service(): View
    {
        return view('pages.site.service', $this->pageData('Services', 'service'));
    }

    public function serviceDetails(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('pages.site.service-details', $this->pageData($service->title, 'service') + [
            'service' => $service,
        ]);
    }

    public function feature(): View
    {
        return view('pages.site.feature', $this->pageData('Our Features', 'feature'));
    }

    public function team(): View
    {
        return view('pages.site.team', $this->pageData('Team Members', 'team'));
    }

    public function testimonial(): View
    {
        return view('pages.site.testimonial', $this->pageData('Testimonial', 'testimonial'));
    }

    public function booking(): View
    {
        return view('pages.site.booking', $this->pageData('Book A Service', 'booking'));
    }

    public function contact(): View
    {
        return view('pages.site.contact', $this->pageData('Contact Us', 'contact'));
    }

    public function bookingAvailability(Request $request, BookingSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['bail', 'required', 'date_format:Y-m-d'],
        ]);

        if ($validated['date'] < $schedule->today()) {
            throw ValidationException::withMessages([
                'date' => 'Please choose today or a future date.',
            ]);
        }

        return response()
            ->json([
                'date' => $validated['date'],
                'timezone' => $schedule->timezone(),
                'slots' => $schedule->availability($validated['date'])->values(),
            ])
            ->header('Cache-Control', 'no-store, private');
    }

    public function storeBooking(Request $request, BookingSchedule $schedule): RedirectResponse
    {
        $preferredTime = $schedule->normalizeTime($request->input('preferred_time'))
            ?? $this->trimStringInput($request, 'preferred_time');

        $request->merge([
            'name' => $this->trimStringInput($request, 'name'),
            'email' => $this->trimStringInput($request, 'email'),
            'phone' => $this->trimStringInput($request, 'phone'),
            'service' => $this->trimStringInput($request, 'service'),
            'preferred_time' => $preferredTime,
            'message' => $this->trimStringInput($request, 'message'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'service' => ['bail', 'required', 'string', Rule::in($this->bookingServiceOptions())],
            'preferred_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$schedule->today(),
            ],
            'preferred_time' => ['bail', 'required', 'string', Rule::in($schedule->slotValues())],
            'message' => ['bail', 'required', 'string', 'min:10', 'max:2000'],
        ]);

        if ($validated['preferred_date'] < $schedule->today()) {
            throw ValidationException::withMessages([
                'preferred_date' => 'Please choose today or a future date.',
            ]);
        }

        if (! $schedule->isFutureSlot($validated['preferred_date'], $validated['preferred_time'])) {
            throw ValidationException::withMessages([
                'preferred_time' => 'That time has already passed. Please choose another available time.',
            ]);
        }

        if (! $schedule->isAvailable($validated['preferred_date'], $validated['preferred_time'])) {
            throw ValidationException::withMessages([
                'preferred_time' => 'That time was just booked. Please choose another available time.',
            ]);
        }

        try {
            DB::transaction(function () use ($request, $schedule, $validated): void {
                Booking::create($validated + [
                    'user_id' => $request->user()->id,
                    'slot_key' => $schedule->slotKey(
                        $validated['preferred_date'],
                        $validated['preferred_time']
                    ),
                    'status' => Booking::STATUS_PENDING,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // The database unique index is the final guard when two customers
            // submit the same apparently-available slot at the same moment.
            throw ValidationException::withMessages([
                'preferred_time' => 'That time was just booked. Please choose another available time.',
            ]);
        }

        return back()->with(
            'success',
            'Your booking has been submitted and the selected time is now reserved.'
        );
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Your message has been saved. We will get back to you soon.');
    }

    private function pageData(string $title, string $key): array
    {
        $schedule = app(BookingSchedule::class);

        return [
            'pageTitle' => $title,
            'pageKey' => $key,
            'activeServices' => Service::active()->oldest()->get(),
            'bookingSlots' => $schedule->slots(),
            'bookingToday' => $schedule->today(),
        ];
    }

    private function bookingServiceOptions(): array
    {
        $services = Service::active()->oldest()->pluck('title')->all();

        return $services !== []
            ? $services
            : ['IT Consultation', 'Web Development', 'App Development', 'Cyber Security'];
    }

    private function trimStringInput(Request $request, string $key): mixed
    {
        $value = $request->input($key);

        return is_string($value) ? trim($value) : $value;
    }
}
