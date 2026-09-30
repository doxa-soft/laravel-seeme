<?php

namespace DoxaSoft\LaravelSeeMe\Contracts;

use DoxaSoft\LaravelSeeMe\Data\SeeMeBalance;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;

interface SeeMeServiceInterface
{
    public function send(string $number, SeeMeMessage $message): SeeMeResult;

    public function balance(): SeeMeBalance;

    public function setIp(string $ip): SeeMeResult;
}
