<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::current();
        return view('pages.contact', compact('settings'));
    }

    public function store(Request $request)
    {
        \App\Support\TurnstileVerifier::verify($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?: 'General Inquiry',
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'is_read' => false,
        ]);

        return redirect()->route('contact.index')->with('success', 'Thank you! Your message has been sent successfully. We will review it and reply as soon as possible.');
    }
}
