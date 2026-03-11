<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('verified user is redirected from verification prompt', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('email can be verified', function (): void {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('already verified user is redirected from verify endpoint', function (): void {
    $user = User::factory()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification notification can be sent', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post('/email/verification-notification');

    Notification::assertSentTo($user, VerifyEmail::class);
    $response->assertRedirect()->assertSessionHas('status', 'verification-link-sent');
});

test('verified user cannot resend verification notification', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/email/verification-notification');

    $response->assertRedirect(route('dashboard', absolute: false));
});
