<?php

namespace App\Http\Controllers;

use App\Support\WebpImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $user->loadCount([
            'prompts as approved_prompts_count' => fn ($q) => $q->where('status', 'approved'),
            'favorites',
            'followers',
            'following',
            'collections',
            'receivedTips',
        ]);

        return view('dashboard.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $uniqueEmail = Rule::unique('users', 'email')->ignore($user->id);
        $uniqueUsername = Rule::unique('users', 'username')->ignore($user->id);

        $cleanUsername = ltrim(trim($request->input('username', '')), '@');
        $request->merge(['username' => $cleanUsername]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/', $uniqueUsername],
            'email' => ['required', 'email', 'max:255', $uniqueEmail],
            'bio' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'tip_link' => ['nullable', 'url', 'max:500'],
            'tip_goal' => ['nullable', 'string', 'max:255'],
            'stripe_account_id' => ['nullable', 'string', 'max:120'],
            'paypal_email' => ['nullable', 'email', 'max:190'],
            'monetization_enabled' => ['nullable', 'boolean'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'replicate_api_token' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $updateData = [
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
            'tip_link' => $data['tip_link'] ?? null,
            'tip_goal' => $data['tip_goal'] ?? null,
            'stripe_account_id' => $data['stripe_account_id'] ?? null,
            'paypal_email' => $data['paypal_email'] ?? null,
            'monetization_enabled' => $request->boolean('monetization_enabled', true),
            'avatar_url' => $data['avatar_url'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $avatarPath = WebpImageStorage::storeUpload($request->file('avatar'), 'avatars');
            $updateData['avatar_url'] = '/storage/' . $avatarPath;
        }

        if ($request->has('openai_api_key')) {
            $updateData['openai_api_key'] = $data['openai_api_key'] ?: null;
        }

        if ($request->has('replicate_api_token')) {
            $updateData['replicate_api_token'] = $data['replicate_api_token'] ?: null;
        }

        // Only update password if user explicitly typed a new one. No existing password required!
        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Your profile, custom handle, and creator monetization settings have been saved successfully.');
    }
}
