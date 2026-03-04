<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

new class extends Component
{
    //
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Requester->value, // Default role
        ]);

        Auth::login($user);

        $this->redirectRoute('dashboard', navigate: true);
    }

};
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow">

        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

        <form wire:submit.prevent="register" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">Name</label>
                <input type="text" wire:model.defer="name"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email" wire:model.defer="email"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password" wire:model.defer="password"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Confirm Password</label>
                <input type="password" wire:model.defer="password_confirmation"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
                Register
            </button>

            <div class="text-center text-sm mt-4">
                <a href="{{ route('login') }}" class="text-indigo-600">
                    Already have an account? Login
                </a>
            </div>

        </form>
    </div>
</div>