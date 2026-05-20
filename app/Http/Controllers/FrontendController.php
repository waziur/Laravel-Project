<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FrontendController extends Controller
{
    public function home(): View
    {
        return view('index', $this->pageData('Home', 'home'));
    }

    public function about(): View
    {
        return view('about', $this->pageData('About Us', 'about'));
    }

    public function service(): View
    {
        return view('service', $this->pageData('Services', 'service'));
    }

    public function feature(): View
    {
        return view('feature', $this->pageData('Our Features', 'feature'));
    }

    public function team(): View
    {
        return view('team', $this->pageData('Team Members', 'team'));
    }

    public function testimonial(): View
    {
        return view('testimonial', $this->pageData('Testimonial', 'testimonial'));
    }

    public function booking(): View
    {
        return view('booking', $this->pageData('Book A Service', 'booking'));
    }

    public function contact(): View
    {
        return view('contact', $this->pageData('Contact Us', 'contact'));
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => $this->trimStringInput($request, 'phone'),
            'service' => $this->trimStringInput($request, 'service'),
            'preferred_time' => $this->trimStringInput($request, 'preferred_time'),
            'message' => $this->trimStringInput($request, 'message'),
        ]);

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'service' => ['bail', 'required', 'string', Rule::in($this->bookingServiceOptions())],
            'preferred_date' => ['bail', 'required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'message' => ['bail', 'required', 'string', 'min:10', 'max:2000'],
        ]);

        $user = $request->user();

        Booking::create($validated + [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => Booking::STATUS_PENDING,
        ]);

        return back()->with('success', 'Your booking has been submitted and is waiting for admin review.');
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
        return [
            'pageTitle' => $title,
            'pageKey' => $key,
            'activeServices' => Service::active()->oldest()->get(),
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
