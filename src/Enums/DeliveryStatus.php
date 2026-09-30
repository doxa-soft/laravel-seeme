<?php

namespace DoxaSoft\LaravelSeeMe\Enums;

enum DeliveryStatus: int
{
    case UndeliverableOtherError = 1;
    case SendingStopped          = 2;
    case TransmittedToSmsc       = 3;
    case AcceptedBySmsc          = 4;
    case RejectedBySmsc          = 5;
    case Delivered               = 6;
    case Undeliverable           = 7;
    case WaitingForDevice        = 8;
    case ValidityExpired         = 9;
    case StatusMissing           = 10;

    public function label(): string
    {
        return match ($this) {
            self::UndeliverableOtherError => 'Undeliverable due to other error',
            self::SendingStopped          => 'Sending stopped',
            self::TransmittedToSmsc       => 'Transmitted to remote SMSC',
            self::AcceptedBySmsc          => 'Accepted by remote SMSC',
            self::RejectedBySmsc          => 'Rejected by remote SMSC',
            self::Delivered               => 'Delivered',
            self::Undeliverable           => 'Undeliverable',
            self::WaitingForDevice        => 'Waiting for target device',
            self::ValidityExpired         => 'Validity period expired',
            self::StatusMissing           => 'Status missing',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::UndeliverableOtherError,
            self::RejectedBySmsc,
            self::Delivered,
            self::Undeliverable,
            self::ValidityExpired,
            self::StatusMissing,
        ]);
    }
}
