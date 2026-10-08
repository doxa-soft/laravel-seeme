<?php

namespace DoxaSoft\LaravelSeeMe;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Notifications\SeeMeChannel;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class SeeMeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seeme.php', 'seeme');

        $this->app->singleton(SeeMeServiceInterface::class, function ($app) {
            if ($app['config']->get('seeme.driver', 'api') === 'log') {
                return new LogSeeMeService();
            }

            return new SeeMeService(
                http: $app->make(HttpFactory::class),
                apiKey: $app['config']->get('seeme.api_key', ''),
                baseUrl: $app['config']->get('seeme.base_url', 'https://seeme.hu/gateway'),
                defaultSender: $app['config']->get('seeme.sender', ''),
            );
        });

        $this->app->singleton(SeeMeChannel::class, function ($app) {
            return new SeeMeChannel($app->make(SeeMeServiceInterface::class));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/seeme.php' => config_path('seeme.php'),
            ], 'seeme-config');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/seeme.php');
    }
}
