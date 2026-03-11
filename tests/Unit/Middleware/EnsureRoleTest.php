<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('allows user with matching role', function (): void {
    $user = User::factory()->admin()->create();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureRole;
    $response = $middleware->handle($request, fn (): Response => new Response('ok'), 'admin');

    expect($response->getContent())->toBe('ok');
});

test('allows user with one of multiple roles', function (): void {
    $user = User::factory()->agent()->create();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureRole;
    $response = $middleware->handle($request, fn (): Response => new Response('ok'), 'agent', 'admin');

    expect($response->getContent())->toBe('ok');
});

test('denies user without matching role', function (): void {
    $user = User::factory()->requester()->create();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);

    $middleware = new EnsureRole;
    $middleware->handle($request, fn (): Response => new Response('ok'), 'admin');
})->throws(HttpException::class);

test('denies unauthenticated user', function (): void {
    $request = Request::create('/test');
    $request->setUserResolver(fn (): null => null);

    $middleware = new EnsureRole;
    $middleware->handle($request, fn (): Response => new Response('ok'), 'admin');
})->throws(HttpException::class);
