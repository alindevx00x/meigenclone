<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:500',
            'message' => 'nullable|string|max:300',
            'prompt_id' => 'nullable|exists:prompts,id',
        ]);

        $tip = Tip::create([
            'sender_id' => $request->user()?->id,
            'creator_id' => $user->id,
            'prompt_id' => $validated['prompt_id'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'USD',
            'message' => $validated['message'] ?? null,
            'status' => 'completed',
            'transaction_reference' => 'TIP_' . strtoupper(Str::random(12)),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Thank you! You sent \${$tip->amount} tip to {$user->name}!",
            'tip' => [
                'id' => $tip->id,
                'amount' => $tip->amount,
                'creator_name' => $user->name,
                'reference' => $tip->transaction_reference,
            ],
        ]);
    }
}
