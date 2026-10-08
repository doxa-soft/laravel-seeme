<?php

namespace DoxaSoft\LaravelSeeMe;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeBalance;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;
use Illuminate\Support\Facades\Log;

class LogSeeMeService implements SeeMeServiceInterface
{
    public function send(string $number, SeeMeMessage $message): SeeMeResult
    {
        Log::debug('SeeMe SMS (log driver)', [
            'to'        => $number,
            'message'   => $message->getContent(),
            'sender'    => $message->getSender(),
            'reference' => $message->getReference(),
        ]);

        return new SeeMeResult('OK', 0, 'Logged successfully');
    }

    public function balance(): SeeMeBalance
    {
        return new SeeMeBalance(0, 'HUF', '0 HUF', 0, 0);
    }

    public function setIp(string $ip): SeeMeResult
    {
        return new SeeMeResult('OK', 0, 'Logged successfully');
    }
}
