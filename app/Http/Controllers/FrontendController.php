<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\QuoteRequest;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FrontendController extends Controller
{
    public function home(): View
    {
        return view('index', $this->pageData('Home', 'home') + [
            'approvedQuotes' => QuoteRequest::approved()->latest()->take(6)->get(),
        ]);
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

    public function quote(): View
    {
        return view('quote', $this->pageData('Free Quote', 'quote'));
    }

    public function contact(): View
    {
        return view('contact', $this->pageData('Contact Us', 'contact'));
    }

    public function storeQuote(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => $this->trimStringInput($request, 'name'),
            'email' => $this->trimStringInput($request, 'email'),
            'service' => $this->trimStringInput($request, 'service'),
            'message' => $this->trimStringInput($request, 'message'),
        ]);

        $validated = $request->validate([
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100'],
            'email' => ['bail', 'required', 'string', 'email', 'max:150'],
            'service' => ['bail', 'required', 'string', Rule::in($this->quoteServiceOptions())],
            'message' => ['bail', 'required', 'string', 'min:10', 'max:2000'],
        ]);

        QuoteRequest::create($validated + [
            'is_approved' => false,
        ]);

        return back()->with('success', 'Your quote request has been saved and is waiting for admin approval.');
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

    private function quoteServiceOptions(): array
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
