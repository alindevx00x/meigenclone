<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TurnstileVerifier
{
    /**
     * Verify Cloudflare Turnstile token from incoming request if enabled.
     *
     * @throws ValidationException
     */
    public static function verify(Request $request): void
    {
        if (! SiteSetting::enabled('cloudflare_turnstile_enabled')) {
            return;
        }

        $secretKey = SiteSetting::value('cloudflare_turnstile_secret_key');
        if (empty($secretKey)) {
            return; // Gracefully bypass if secret key is not configured in admin
        }

        $token = $request->input('cf-turnstile-response');

        if (empty($token)) {
            throw ValidationException::withMessages([
                'turnstile' => 'Please complete the Cloudflare anti-bot verification.',
            ]);
        }

        try {
            $response = Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            if (! $response->json('success')) {
                Log::warning('Cloudflare Turnstile check failed', [
                    'ip' => $request->ip(),
                    'errors' => $response->json('error-codes', []),
                ]);
                throw ValidationException::withMessages([
                    'turnstile' => 'Anti-bot security verification failed. Please refresh and try again.',
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Cloudflare Turnstile connection exception: ' . $e->getMessage());
            // Allow submission if Cloudflare verification endpoint is unreachable
        }
    }
}
