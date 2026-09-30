<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Feature;

use DoxaSoft\LaravelSeeMe\Enums\DeliveryStatus;
use DoxaSoft\LaravelSeeMe\Events\DeliveryReportReceived;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class DeliveryReportWebhookTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('seeme.webhooks.delivery_report.enabled', true);
        $app['config']->set('seeme.webhooks.delivery_report.path', 'seeme/delivery-report');
    }

    public function test_delivery_report_fires_event(): void
    {
        Event::fake([DeliveryReportReceived::class]);

        $this->get('/seeme/delivery-report?'.http_build_query([
            'reference' => 'ref-123',
            'number'    => '36201234567',
            'sender'    => 'TestSender',
            'code'      => 6,
            'message'   => 'Delivered',
            'price'     => 5.0,
            'timestamp' => '20240101120000',
            'mccmnc'    => 21630,
            'split'     => 1,
        ]))->assertOk();

        Event::assertDispatched(DeliveryReportReceived::class, function (DeliveryReportReceived $event) {
            return $event->reference === 'ref-123'
                && $event->number === '36201234567'
                && $event->status === DeliveryStatus::Delivered
                && $event->price === 5.0
                && $event->mccmnc === 21630;
        });
    }

    public function test_delivery_report_without_optional_fields(): void
    {
        Event::fake([DeliveryReportReceived::class]);

        $this->get('/seeme/delivery-report?'.http_build_query([
            'number'    => '36201234567',
            'sender'    => 'TestSender',
            'code'      => 3,
            'message'   => 'Transmitted',
            'timestamp' => '20240101120000',
        ]))->assertOk();

        Event::assertDispatched(DeliveryReportReceived::class, function (DeliveryReportReceived $event) {
            return $event->reference === null
                && $event->mccmnc === null
                && $event->status === DeliveryStatus::TransmittedToSmsc;
        });
    }

    public function test_ip_middleware_blocks_unauthorized_ip(): void
    {
        $this->app['config']->set('seeme.webhooks.allowed_ips', '1.2.3.4');

        $this->get('/seeme/delivery-report?code=6&number=36201234567&sender=x&timestamp=20240101&message=ok')
            ->assertForbidden();
    }

    public function test_ip_middleware_allows_when_not_configured(): void
    {
        Event::fake([DeliveryReportReceived::class]);

        $this->app['config']->set('seeme.webhooks.allowed_ips', null);

        $this->get('/seeme/delivery-report?'.http_build_query([
            'number'    => '36201234567',
            'sender'    => 'x',
            'code'      => 6,
            'message'   => 'Delivered',
            'timestamp' => '20240101120000',
        ]))->assertOk();
    }
}
