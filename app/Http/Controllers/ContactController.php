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
        // Cloudflare Turnstile verification
        if (SiteSetting::enabled('cloudflare_turnstile_enabled')) {
            $secretKey = SiteSetting::value('cloudflare_turnstile_secret_key');
            $turnstileToken = $request->input('cf-turnstile-response');

            if (! empty($secretKey)) {
                if (empty($turnstileToken)) {
                    throw ValidationException::withMessages([
                        'turnstile' => 'Please complete the Cloudflare anti-bot security verification before submitting.',
                    ]);
                }

                try {
                    $verifyResponse = Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                        'secret' => $secretKey,
                        'response' => $turnstileToken,
                        'remoteip' => $request->ip(),
                    ]);

                    if (! $verifyResponse->json('success')) {
                        Log::warning('Cloudflare Turnstile verification rejected', [
                            'ip' => $request->ip(),
                            'errors' => $verifyResponse->json('error-codes', []),
                        ]);
                        throw ValidationException::withMessages([
                            'turnstile' => 'Anti-bot verification failed. Please refresh the page and try again.',
                        ]);
                    }
                } catch (ValidationException $e) {
                    throw $e;
                } catch (\Throwable $e) {
                    Log::error('Cloudflare Turnstile connection error: ' . $e->getMessage());
                    // Allow message through if Cloudflare API is temporarily unreachable
                }
            }
        }

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
