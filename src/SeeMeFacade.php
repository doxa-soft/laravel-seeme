<?php

namespace DoxaSoft\LaravelSeeMe;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeBalance;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;
use DoxaSoft\LaravelSeeMe\Support\SeeMeFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static SeeMeResult  send(string $number, SeeMeMessage $message)
 * @method static SeeMeBalance balance()
 * @method static SeeMeResult  setIp(string $ip)
 */
class SeeMeFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeeMeServiceInterface::class;
    }

    public static function fake(): SeeMeFake
    {
        $fake = new SeeMeFake();
        static::swap($fake);

        return $fake;
    }
}
