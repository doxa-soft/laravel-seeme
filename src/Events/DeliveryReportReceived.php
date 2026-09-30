<?php

namespace DoxaSoft\LaravelSeeMe\Events;

use DoxaSoft\LaravelSeeMe\Enums\DeliveryStatus;
use Illuminate\Foundation\Events\Dispatchable;

class DeliveryReportReceived
{
    use Dispatchable;

    public function __construct(
        public readonly ?string $reference,
        public readonly string $number,
        public readonly string $sender,
        public readonly DeliveryStatus $status,
        public readonly string $statusMessage,
        public readonly float $price,
        public readonly string $timestamp,
        public readonly ?int $mccmnc,
        public readonly int $split,
    ) {}
}
