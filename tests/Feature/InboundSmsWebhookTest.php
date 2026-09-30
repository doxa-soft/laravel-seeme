<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Feature;

use DoxaSoft\LaravelSeeMe\Events\InboundSmsReceived;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class InboundSmsWebhookTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('seeme.webhooks.inbound_sms.enabled', true);
        $app['config']->set('seeme.webhooks.inbound_sms.path', 'seeme/inbound');
    }

    public function test_inbound_sms_fires_event(): void
    {
        Event::fake([InboundSmsReceived::class]);

        $this->get('/seeme/inbound?'.http_build_query([
            'message'     => 'Hello World',
            'number'      => '36301234567',
            'destination' => '36207654321',
            'timestamp'   => '20131001122341',
        ]))->assertOk();

        Event::assertDispatched(InboundSmsReceived::class, function (InboundSmsReceived $event) {
            return $event->message === 'Hello World'
                && $event->number === '36301234567'
                && $event->destination === '36207654321'
                && $event->timestamp === '20131001122341';
        });
    }

    public function test_ip_middleware_blocks_unauthorized_ip(): void
    {
        $this->app['config']->set('seeme.webhooks.allowed_ips', '1.2.3.4');

        $this->get('/seeme/inbound?message=Hi&number=36301234567&destination=36207654321&timestamp=20240101')
            ->assertForbidden();
    }
}
