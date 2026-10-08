<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Unit;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\LogSeeMeService;
use DoxaSoft\LaravelSeeMe\SeeMeFacade;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;

class LogSeeMeServiceTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('seeme.driver', 'log');
    }

    #[Test]
    public function containerResolvesLogServiceWhenDriverIsLog(): void
    {
        $service = $this->app->make(SeeMeServiceInterface::class);

        $this->assertInstanceOf(LogSeeMeService::class, $service);
    }

    #[Test]
    public function sendWritesDebugLogEntry(): void
    {
        Log::spy();

        $service = new LogSeeMeService();
        $message = SeeMeMessage::create('Hello from log driver')->reference('ref-42');

        $result = $service->send('36201234567', $message);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with('SeeMe SMS (log driver)', [
                'to'        => '36201234567',
                'message'   => 'Hello from log driver',
                'sender'    => null,
                'reference' => 'ref-42',
            ]);

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function sendMakesNoHttpRequest(): void
    {
        Http::fake();

        new LogSeeMeService()->send('36201234567', SeeMeMessage::create('Test'));

        Http::assertNothingSent();
    }

    #[Test]
    public function facadeFakeStillWorksIndependentlyOfDriver(): void
    {
        $fake = SeeMeFacade::fake();

        SeeMeFacade::send('36201234567', SeeMeMessage::create('Fake test'));

        $fake->assertSentTo('36201234567');
        $fake->assertSentCount(1);
    }
}
