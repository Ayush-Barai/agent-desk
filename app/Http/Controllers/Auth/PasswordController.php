<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final class PasswordController
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        assert($user instanceof User);

        $password = $validated['password'];
        assert(is_string($password));

        $user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('status', 'password-updated');
    }
}
