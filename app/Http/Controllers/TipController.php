<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TipController extends Controller
{
    public function store(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if (! $user->monetization_enabled) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This creator has disabled tipping.',
                ], 422);
            }
            return back()->with('error', 'This creator has disabled tipping.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:500'],
            'message' => ['nullable', 'string', 'max:300'],
            'prompt_id' => ['nullable', 'exists:prompts,id'],
            'payment_gateway' => ['nullable', 'string', 'in:platform,stripe,buymeacoffee,kofi'],
            'tipper_name' => ['nullable', 'string', 'max:80'],
        ]);

        $sender = $request->user();
        $tipperName = $validated['tipper_name'] ?? ($sender?->name ?: 'Community Supporter');
        $gateway = $validated['payment_gateway'] ?? 'platform';

        // Check if real Stripe Checkout is requested
        if ($gateway === 'stripe') {
            $stripeSecret = \App\Models\SiteSetting::value('stripe_secret_key');
            if ($stripeSecret) {
                try {
                    $sessionParams = [
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'usd',
                                'unit_amount' => (int) round($validated['amount'] * 100),
                                'product_data' => [
                                    'name' => 'Tip for ' . $user->name,
                                    'description' => $validated['message'] ?: 'Community Tip & Creator Support',
                                ],
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'success_url' => route('tips.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('creators.show', $user) . '?tip_status=cancelled',
                        'metadata' => [
                            'creator_id' => (string) $user->id,
                            'tipper_name' => $tipperName,
                            'sender_id' => (string) ($sender?->id ?? ''),
                            'prompt_id' => (string) ($validated['prompt_id'] ?? ''),
                            'message' => (string) ($validated['message'] ?? ''),
                        ],
                    ];

                    // If creator has connected their Stripe payout account
                    if (! empty($user->stripe_account_id)) {
                        $sessionParams['payment_intent_data'] = [
                            'transfer_data' => [
                                'destination' => $user->stripe_account_id,
                            ],
                        ];
                    }

                    $response = \Illuminate\Support\Facades\Http::withBasicAuth($stripeSecret, '')
                        ->asForm()
                        ->post('https://api.stripe.com/v1/checkout/sessions', $sessionParams);

                    if ($response->successful()) {
                        $checkoutUrl = $response->json('url');
                        if ($request->expectsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => true,
                                'redirect_url' => $checkoutUrl,
                                'message' => 'Redirecting to Stripe Checkout…',
                            ]);
                        }
                        return redirect()->away($checkoutUrl);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Stripe session failed: ' . $e->getMessage());
                }
            }
        }

        // Check if PayPal is requested
        if ($gateway === 'paypal') {
            $paypalClientId = \App\Models\SiteSetting::value('paypal_client_id');
            $paypalSecret = \App\Models\SiteSetting::value('paypal_secret');
            $paypalMode = \App\Models\SiteSetting::value('paypal_mode', 'sandbox');
            $baseUrl = $paypalMode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

            if ($paypalClientId && $paypalSecret) {
                try {
                    $tokenRes = \Illuminate\Support\Facades\Http::withBasicAuth($paypalClientId, $paypalSecret)
                        ->asForm()
                        ->post($baseUrl . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

                    if ($tokenRes->successful()) {
                        $accessToken = $tokenRes->json('access_token');
                        $orderRes = \Illuminate\Support\Facades\Http::withToken($accessToken)
                            ->post($baseUrl . '/v2/checkout/orders', [
                                'intent' => 'CAPTURE',
                                'purchase_units' => [[
                                    'amount' => [
                                        'currency_code' => 'USD',
                                        'value' => number_format((float) $validated['amount'], 2, '.', ''),
                                    ],
                                    'description' => 'Tip for ' . $user->name,
                                    'custom_id' => json_encode([
                                        'creator_id' => $user->id,
                                        'sender_id' => $sender?->id,
                                        'prompt_id' => $validated['prompt_id'] ?? null,
                                        'tipper_name' => $tipperName,
                                        'message' => $validated['message'] ?? null,
                                    ]),
                                ]],
                                'application_context' => [
                                    'return_url' => route('tips.paypal.capture'),
                                    'cancel_url' => route('creators.show', $user) . '?tip_status=cancelled',
                                    'brand_name' => ($settings['site_name'] ?? 'PromptImage') . ' Creator Tip',
                                ],
                            ]);

                        if ($orderRes->successful()) {
                            $links = collect($orderRes->json('links'));
                            $approveLink = $links->firstWhere('rel', 'approve')['href'] ?? null;
                            if ($approveLink) {
                                if ($request->expectsJson() || $request->ajax()) {
                                    return response()->json([
                                        'success' => true,
                                        'redirect_url' => $approveLink,
                                        'message' => 'Redirecting to PayPal…',
                                    ]);
                                }
                                return redirect()->away($approveLink);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('PayPal order failed: ' . $e->getMessage());
                }
            }
        }

        // Standard instant tip (Platform / simulated / direct)
        $tip = Tip::create([
            'sender_id' => $sender?->id,
            'creator_id' => $user->id,
            'prompt_id' => $validated['prompt_id'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'USD',
            'payment_gateway' => $gateway,
            'tipper_name' => $tipperName,
            'message' => $validated['message'] ?? null,
            'status' => 'completed',
            'transaction_reference' => 'TIP_' . strtoupper(Str::random(10)),
        ]);

        // Increment creator's cumulative earnings
        $user->increment('tips_earned_total', $validated['amount']);

        $thankYouMessage = "Thank you! You sent a \${$tip->amount} tip to {$user->name}!";
        if ($user->tip_message) {
            $thankYouMessage .= " Creator note: \"" . $user->tip_message . "\"";
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $thankYouMessage,
                'tip' => [
                    'id' => $tip->id,
                    'amount' => number_format((float) $tip->amount, 2),
                    'creator_name' => $user->name,
                    'creator_handle' => $user->handle,
                    'reference' => $tip->transaction_reference,
                    'gateway' => $gateway,
                ],
                'creator_total_tips' => number_format((float) $user->fresh()->tips_earned_total, 2),
                'creator_tips_count' => $user->receivedTips()->count(),
            ]);
        }

        return back()->with('success', $thankYouMessage);
    }

    /**
     * Handle return from successful Stripe Checkout
     */
    public function stripeSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $stripeSecret = \App\Models\SiteSetting::value('stripe_secret_key');

        if ($sessionId && $stripeSecret) {
            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($stripeSecret, '')
                    ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

                if ($response->successful()) {
                    $session = $response->json();
                    $creatorId = $session['metadata']['creator_id'] ?? null;
                    $creator = $creatorId ? User::find($creatorId) : null;

                    if ($creator) {
                        $amount = ($session['amount_total'] ?? 0) / 100;
                        Tip::firstOrCreate(
                            ['transaction_reference' => $sessionId],
                            [
                                'sender_id' => $session['metadata']['sender_id'] ?: null,
                                'creator_id' => $creator->id,
                                'prompt_id' => $session['metadata']['prompt_id'] ?: null,
                                'amount' => $amount,
                                'currency' => strtoupper($session['currency'] ?? 'USD'),
                                'payment_gateway' => 'stripe',
                                'tipper_name' => $session['metadata']['tipper_name'] ?: 'Stripe Supporter',
                                'message' => $session['metadata']['message'] ?: null,
                                'status' => 'completed',
                            ]
                        );

                        $creator->increment('tips_earned_total', $amount);

                        return redirect()->route('creators.show', $creator)
                            ->with('success', "Thank you! Your \${$amount} tip via Stripe Checkout was received!");
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Stripe success verification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('prompts.index')->with('success', 'Your tip was successfully completed. Thank you for supporting our creators!');
    }

    /**
     * Handle return from successful PayPal checkout
     */
    public function paypalCapture(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        $paypalClientId = \App\Models\SiteSetting::value('paypal_client_id');
        $paypalSecret = \App\Models\SiteSetting::value('paypal_secret');
        $paypalMode = \App\Models\SiteSetting::value('paypal_mode', 'sandbox');
        $baseUrl = $paypalMode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        if ($token && $paypalClientId && $paypalSecret) {
            try {
                $tokenRes = \Illuminate\Support\Facades\Http::withBasicAuth($paypalClientId, $paypalSecret)
                    ->asForm()
                    ->post($baseUrl . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

                if ($tokenRes->successful()) {
                    $accessToken = $tokenRes->json('access_token');
                    $captureRes = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("{$baseUrl}/v2/checkout/orders/{$token}/capture", (object) []);

                    if ($captureRes->successful()) {
                        $order = $captureRes->json();
                        $unit = $order['purchase_units'][0] ?? [];
                        $custom = json_decode($unit['custom_id'] ?? '{}', true);
                        $creator = ! empty($custom['creator_id']) ? User::find($custom['creator_id']) : null;
                        $amount = (float) ($unit['payments']['captures'][0]['amount']['value'] ?? 5.00);

                        if ($creator) {
                            Tip::firstOrCreate(
                                ['transaction_reference' => 'PAYPAL_' . $token],
                                [
                                    'sender_id' => $custom['sender_id'] ?? null,
                                    'creator_id' => $creator->id,
                                    'prompt_id' => $custom['prompt_id'] ?? null,
                                    'amount' => $amount,
                                    'currency' => 'USD',
                                    'payment_gateway' => 'paypal',
                                    'tipper_name' => $custom['tipper_name'] ?? 'PayPal Supporter',
                                    'message' => $custom['message'] ?? null,
                                    'status' => 'completed',
                                ]
                            );

                            $creator->increment('tips_earned_total', $amount);

                            return redirect()->route('creators.show', $creator)
                                ->with('success', "Thank you! Your \${$amount} tip via PayPal was received!");
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PayPal capture failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('prompts.index')->with('success', 'Your PayPal tip was processed. Thank you!');
    }

    /**
     * Webhook receiver for Stripe Checkout / Lemon Squeezy / Buy Me A Coffee
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $rawContent = $request->getContent();
        $webhookSecret = \App\Models\SiteSetting::value('stripe_webhook_secret');

        // Verify Stripe signature if secret is configured
        if (! empty($webhookSecret)) {
            $sigHeader = $request->header('Stripe-Signature');
            if (! $sigHeader) {
                Log::warning('Stripe webhook missing signature header.');
                return response()->json(['error' => 'Missing signature'], 401);
            }

            $sigParts = [];
            foreach (explode(',', $sigHeader) as $part) {
                $sub = explode('=', trim($part), 2);
                if (count($sub) === 2) {
                    $sigParts[$sub[0]][] = $sub[1];
                }
            }

            $timestamp = $sigParts['t'][0] ?? null;
            $signatures = $sigParts['v1'] ?? [];

            if (! $timestamp || empty($signatures)) {
                return response()->json(['error' => 'Malformed signature header'], 401);
            }

            $signedPayload = $timestamp . '.' . $rawContent;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);

            $matched = false;
            foreach ($signatures as $sig) {
                if (hash_equals($expectedSignature, $sig)) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                Log::warning('Stripe webhook signature mismatch.');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        Log::info('Creator Tip Webhook received and verified:', $payload);

        // Extract transaction attributes from common provider formats
        $creatorIdentifier = $request->input('creator_id')
            ?? $request->input('data.object.metadata.creator_id')
            ?? $request->input('data.object.client_reference_id')
            ?? $request->input('creator_username');

        $amount = (float) ($request->input('amount')
            ?? (($request->input('data.object.amount_total') ?? 0) / 100)
            ?? $request->input('data.attributes.total')
            ?? 0);

        if (! $creatorIdentifier || $amount <= 0) {
            return response()->json(['received' => true, 'note' => 'Ignored non-tip or zero event']);
        }

        $creator = is_numeric($creatorIdentifier)
            ? User::find($creatorIdentifier)
            : User::where('username', ltrim($creatorIdentifier, '@'))->first();

        if (! $creator) {
            return response()->json(['received' => true, 'error' => 'Creator not found'], 404);
        }

        $tip = Tip::create([
            'creator_id' => $creator->id,
            'amount' => $amount,
            'currency' => strtoupper($request->input('currency') ?? 'USD'),
            'payment_gateway' => $request->input('gateway', 'stripe'),
            'tipper_name' => $request->input('customer_name') ?? $request->input('data.object.customer_details.name') ?? 'External Supporter',
            'message' => $request->input('message') ?? $request->input('data.object.metadata.message'),
            'status' => 'completed',
            'transaction_reference' => $request->input('id') ?? ('WH_' . strtoupper(Str::random(12))),
        ]);

        $creator->increment('tips_earned_total', $amount);

        return response()->json(['received' => true, 'tip_id' => $tip->id]);
    }
}
