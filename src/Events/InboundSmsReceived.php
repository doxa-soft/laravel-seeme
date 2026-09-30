<?php

namespace DoxaSoft\LaravelSeeMe\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InboundSmsReceived
{
    use Dispatchable;

    public function __construct(
        public readonly string $message,
        public readonly string $number,
        public readonly string $destination,
        public readonly string $timestamp,
    ) {}
}
