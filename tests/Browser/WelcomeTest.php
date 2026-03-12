<?php

declare(strict_types=1);

it('has welcome page', function (): void {
    $this->get('/')->assertOk();
});
