<?php

namespace DoxaSoft\LaravelSeeMe\Support;

use Closure;
use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeBalance;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;
use PHPUnit\Framework\Assert as PHPUnit;

class SeeMeFake implements SeeMeServiceInterface
{
    /** @var array<array{string, SeeMeMessage}> */
    private array $sent = [];

    public function send(string $number, SeeMeMessage $message): SeeMeResult
    {
        $this->sent[] = [$number, $message];

        return new SeeMeResult('OK', 0, 'Faked successfully');
    }

    public function balance(): SeeMeBalance
    {
        return new SeeMeBalance(0, 'HUF', '0 HUF', 0, 0);
    }

    public function setIp(string $ip): SeeMeResult
    {
        return new SeeMeResult('OK', 0, 'Faked successfully');
    }

    public function assertSent(Closure $callback): void
    {
        PHPUnit::assertTrue(
            collect($this->sent)->contains(fn ($entry) => $callback($entry[1], $entry[0])),
            'The expected SMS was not sent.',
        );
    }

    public function assertSentTo(string $number): void
    {
        PHPUnit::assertTrue(
            collect($this->sent)->contains(fn ($entry) => $entry[0] === $number),
            "Expected an SMS to be sent to [{$number}], but it was not.",
        );
    }

    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->sent, sprintf(
            'Unexpected SMS(es) were sent: %d sent.',
            count($this->sent),
        ));
    }

    public function assertSentCount(int $count): void
    {
        $actual = count($this->sent);

        PHPUnit::assertCount(
            $count,
            $this->sent,
            "Expected [{$count}] SMS(es) to be sent, but [{$actual}] were sent.",
        );
    }
}
