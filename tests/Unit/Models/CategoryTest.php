<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Ticket;

test('category can be created via factory', function (): void {
    $category = Category::factory()->create();

    expect($category->id)->toBeString()
        ->and($category->name)->toBeString()
        ->and($category->is_active)->toBeTrue();
});

test('inactive factory state', function (): void {
    $category = Category::factory()->inactive()->create();

    expect($category->is_active)->toBeFalse();
});

test('category has many tickets', function (): void {
    $category = Category::factory()->create();
    Ticket::factory()->create(['category_id' => $category->id]);

    expect($category->tickets)->toHaveCount(1)
        ->and($category->tickets->first())->toBeInstanceOf(Ticket::class);
});
