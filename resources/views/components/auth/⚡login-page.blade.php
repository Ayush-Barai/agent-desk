<?php


declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    //
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember
        )) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }
};

?>
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow">

        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <form wire:submit.prevent="login" class="space-y-4">

            <div>
                <label class="block text-sm font-medium">Email</label>
                <input type="email"
                       wire:model.defer="email"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password"
                       wire:model.defer="password"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" wire:model="remember" class="mr-2">
                <span class="text-sm">Remember me</span>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
                Login
            </button>

            <div class="text-center text-sm mt-4">
                <a href="{{ route('register') }}" class="text-indigo-600">
                    Don't have an account? Register
                </a>
            </div>

        </form>
    </div>
</div>