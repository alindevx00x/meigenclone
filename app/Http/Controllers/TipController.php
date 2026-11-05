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
     * Webhook receiver for Stripe Checkout / Lemon Squeezy / Buy Me A Coffee
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Creator Tip Webhook received:', $payload);

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
