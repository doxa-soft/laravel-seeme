<?php

namespace DoxaSoft\LaravelSeeMe\Data;

class SeeMeBalance
{
    public function __construct(
        public readonly int $balance,
        public readonly string $currency,
        public readonly string $balanceCurrency,
        public readonly int $customServices,
        public readonly int $monthlySpentBalance,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            balance: (int) ($data['balance'] ?? 0),
            currency: $data['currency'] ?? '',
            balanceCurrency: $data['balance-currency'] ?? '',
            customServices: (int) ($data['custom-services'] ?? 0),
            monthlySpentBalance: (int) ($data['monthlySpentBalance'] ?? 0),
        );
    }
}
