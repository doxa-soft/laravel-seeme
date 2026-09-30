<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Unit;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Exceptions\SeeMeException;
use DoxaSoft\LaravelSeeMe\SeeMeService;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class SeeMeServiceTest extends TestCase
{
    public function test_send_makes_correct_get_request(): void
    {
        Http::fake(['*' => Http::response([
            'result'  => 'OK',
            'code'    => 0,
            'message' => 'SMS submitted',
            'price'   => 5,
        ])]);

        $this->app->make(SeeMeServiceInterface::class)
            ->send('36201234567', SeeMeMessage::create('Hello'));

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), 'https://seeme.hu/gateway')
                && $request['number'] === '36201234567'
                && $request['message'] === 'Hello'
                && $request['format'] === 'json'
                && $request['apiVersion'] === '2.0.1';
        });
    }

    public function test_send_includes_optional_fields(): void
    {
        Http::fake(['*' => Http::response(['result' => 'OK', 'code' => 0, 'message' => '', 'price' => 0])]);

        $message = SeeMeMessage::create('Test')
            ->sender('Brand')
            ->reference('ref-99')
            ->withCallbacks('3,6,7')
            ->callbackUrl('https://example.com/cb');

        $this->app->make(SeeMeServiceInterface::class)
            ->send('36201234567', $message);

        Http::assertSent(function (Request $request) {
            return $request['sender'] === 'Brand'
                && $request['reference'] === 'ref-99'
                && $request['callback'] === '3,6,7'
                && $request['callbackurl'] === 'https://example.com/cb';
        });
    }

    public function test_send_uses_default_sender_from_config(): void
    {
        Http::fake(['*' => Http::response(['result' => 'OK', 'code' => 0, 'message' => '', 'price' => 0])]);

        $this->app->make(SeeMeServiceInterface::class)
            ->send('36201234567', SeeMeMessage::create('Hello'));

        Http::assertSent(fn (Request $r) => $r['sender'] === 'TestSender');
    }

    public function test_send_returns_result_dto(): void
    {
        Http::fake(['*' => Http::response(['result' => 'OK', 'code' => 0, 'message' => 'SMS submitted', 'price' => 7])]);

        $result = $this->app->make(SeeMeServiceInterface::class)
            ->send('36201234567', SeeMeMessage::create('Hello'));

        $this->assertTrue($result->isOk());
        $this->assertEquals(7.0, $result->price);
    }

    public function test_send_throws_seeme_exception_on_api_error(): void
    {
        Http::fake(['*' => Http::response([
            'result'  => 'ERR',
            'code'    => 7,
            'message' => 'Insufficient balance',
            'price'   => 0,
        ])]);

        $this->expectException(SeeMeException::class);
        $this->expectExceptionCode(7);
        $this->expectExceptionMessage('Insufficient balance');

        $this->app->make(SeeMeServiceInterface::class)
            ->send('36201234567', SeeMeMessage::create('Hello'));
    }

    public function test_balance_returns_balance_dto(): void
    {
        Http::fake(['*' => Http::response([
            'result'               => 'OK',
            'balance'              => '155500',
            'currency'             => 'HUF',
            'balance-currency'     => '155500 HUF',
            'custom-services'      => '0',
            'monthlySpentBalance'  => '480000',
        ])]);

        $balance = $this->app->make(SeeMeServiceInterface::class)->balance();

        $this->assertEquals(155500, $balance->balance);
        $this->assertEquals('HUF', $balance->currency);
        $this->assertEquals('155500 HUF', $balance->balanceCurrency);
        $this->assertEquals(480000, $balance->monthlySpentBalance);
    }

    public function test_set_ip_sends_correct_params(): void
    {
        Http::fake(['*' => Http::response(['result' => 'OK', 'code' => 0, 'message' => 'IP set'])]);

        $this->app->make(SeeMeServiceInterface::class)->setIp('1.2.3.4');

        Http::assertSent(function (Request $request) {
            return $request['method'] === 'setip'
                && $request['ip'] === '1.2.3.4';
        });
    }

    public function test_set_ip_throws_on_invalid_ip(): void
    {
        $this->expectException(SeeMeException::class);
        $this->expectExceptionCode(15);

        $this->app->make(SeeMeServiceInterface::class)->setIp('not-an-ip');
    }

    public function test_invalid_api_key_throws_on_construction(): void
    {
        $this->expectException(SeeMeException::class);
        $this->expectExceptionCode(18);

        new SeeMeService(
            $this->app->make(HttpFactory::class),
            'invalidkeynochecksumXXXX',
            'https://seeme.hu/gateway',
            '',
        );
    }

    public function test_empty_api_key_skips_validation(): void
    {
        $service = new SeeMeService(
            $this->app->make(HttpFactory::class),
            '',
            'https://seeme.hu/gateway',
            '',
        );

        $this->assertInstanceOf(SeeMeService::class, $service);
    }
}
