<?php

namespace DoxaSoft\LaravelSeeMe\Tests;

use DoxaSoft\LaravelSeeMe\SeeMeFacade;
use DoxaSoft\LaravelSeeMe\SeeMeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SeeMeServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['SeeMe' => SeeMeFacade::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('seeme.api_key', $this->makeValidApiKey());
        $app['config']->set('seeme.base_url', 'https://seeme.hu/gateway');
        $app['config']->set('seeme.sender', 'TestSender');
    }

    protected function makeValidApiKey(): string
    {
        $key = 'testkey1234';
        $checksum = substr(md5($key), 0, 4);

        return $key.$checksum;
    }
}
