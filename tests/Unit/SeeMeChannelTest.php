<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Unit;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;
use DoxaSoft\LaravelSeeMe\Notifications\SeeMeChannel;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use Illuminate\Notifications\Notification;
use Mockery;

class SeeMeChannelTest extends TestCase
{
    public function test_sends_message_to_routed_number(): void
    {
        $service = Mockery::mock(SeeMeServiceInterface::class);
        $service->shouldReceive('send')
            ->once()
            ->with('36201234567', Mockery::type(SeeMeMessage::class))
            ->andReturn(new SeeMeResult('OK', 0, 'OK'));

        $notifiable = new class {
            public function routeNotificationFor(string $channel, mixed $notification): string
            {
                return '36201234567';
            }
        };

        $notification = new class extends Notification {
            public function toSeeMe(mixed $notifiable): SeeMeMessage
            {
                return SeeMeMessage::create('Your order has been shipped.');
            }
        };

        (new SeeMeChannel($service))->send($notifiable, $notification);
    }

    public function test_skips_send_when_no_number_routed(): void
    {
        $service = Mockery::mock(SeeMeServiceInterface::class);
        $service->shouldNotReceive('send');

        $notifiable = new class {
            public function routeNotificationFor(string $channel, mixed $notification): ?string
            {
                return null;
            }
        };

        $notification = new class extends Notification {
            public function toSeeMe(mixed $notifiable): SeeMeMessage
            {
                return SeeMeMessage::create('Hello');
            }
        };

        (new SeeMeChannel($service))->send($notifiable, $notification);
    }
}
