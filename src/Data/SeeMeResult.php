<?php

namespace DoxaSoft\LaravelSeeMe\Data;

class SeeMeResult
{
    public function __construct(
        public readonly string $result,
        public readonly int $code,
        public readonly string $message,
        public readonly float $price = 0.0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            result: $data['result'] ?? 'ERR',
            code: (int) ($data['code'] ?? 0),
            message: $data['message'] ?? '',
            price: (float) ($data['price'] ?? 0),
        );
    }

    public function isOk(): bool
    {
        return $this->result === 'OK';
    }
}
