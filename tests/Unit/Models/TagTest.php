<?php

declare(strict_types=1);

use App\Models\Tag;
use App\Models\Ticket;

test('tag can be created via factory', function (): void {
    $tag = Tag::factory()->create();

    expect($tag->id)->toBeString()
        ->and($tag->name)->toBeString()
        ->and($tag->color)->toBeString();
});

test('tag belongs to many tickets', function (): void {
    $tag = Tag::factory()->create();
    $ticket = Ticket::factory()->create();
    $ticket->tags()->attach($tag);

    expect($tag->tickets)->toHaveCount(1)
        ->and($tag->tickets->first())->toBeInstanceOf(Ticket::class);
});
