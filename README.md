# Laravel SeeMe

Laravel package for the [SeeMe SMS Gateway](https://seeme.hu). Provides a service, facade, notification channel, and webhook handling for delivery reports and inbound SMS messages.

## Requirements

- PHP 8.2+
- Laravel 11.x

## Installation

### From Packagist

```bash
composer require doxa-soft/laravel-seeme
```

The service provider and `SeeMe` facade alias are registered automatically via package discovery.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=seeme-config
```

Add the following to your `.env`:

```env
SEEME_API_KEY=your-api-key-here
SEEME_SENDER=YourBrand
```

### All available `.env` variables

| Variable | Default | Description |
|---|---|---|
| `SEEME_API_KEY` | — | API key from SeeMe Gateway Settings |
| `SEEME_SENDER` | `""` | Default sender ID shown on recipient's phone |
| `SEEME_BASE_URL` | `https://seeme.hu/gateway` | Gateway URL |
| `SEEME_CALLBACK_IP` | `null` | SeeMe's callback IP (see "Callback forrás" in admin). Leave null to skip IP validation |
| `SEEME_DELIVERY_REPORT_ENABLED` | `false` | Enable delivery report webhook |
| `SEEME_DELIVERY_REPORT_PATH` | `seeme/delivery-report` | URL path for the delivery report webhook |
| `SEEME_INBOUND_SMS_ENABLED` | `false` | Enable inbound SMS webhook |
| `SEEME_INBOUND_SMS_PATH` | `seeme/inbound` | URL path for the inbound SMS webhook |

## Usage

### Facade

```php
use DoxaSoft\LaravelSeeMe\SeeMeFacade as SeeMe;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;

SeeMe::send('36201234567', SeeMeMessage::create('Your order has been shipped.'));
```

### Fluent message builder

```php
$message = SeeMeMessage::create('Rendelésed sikeresen feladásra került.')
    ->sender('WebShop')           // override default sender
    ->reference('order-123')      // your own reference ID, returned in callbacks
    ->withAllCallbacks()          // request all delivery status callbacks
    ->callbackUrl('https://your-app.com/seeme/delivery-report');  // override webhook URL

SeeMe::send('36201234567', $message);
```

Or request only specific delivery statuses:

```php
SeeMeMessage::create('Hello')->withCallbacks('3,6,7'); // transmitted, delivered, undeliverable
```

### Service injection

```php
use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;

class OrderService
{
    public function __construct(private readonly SeeMeServiceInterface $seeMe) {}

    public function notifyCustomer(string $phone, string $message): void
    {
        $this->seeMe->send($phone, SeeMeMessage::create($message));
    }
}
```

### Check balance

```php
$balance = SeeMe::balance();

echo $balance->balance;           // e.g. 155500
echo $balance->currency;          // HUF
echo $balance->balanceCurrency;   // "155500 HUF"
echo $balance->monthlySpentBalance;
```

### Set allowed IP

```php
SeeMe::setIp('84.225.71.53');
```

## Notification Channel

### 1. Add routing to your notifiable model

```php
use DoxaSoft\LaravelSeeMe\Notifications\SeeMeChannel;

class User extends Authenticatable
{
    public function routeNotificationForSeeMe(): string
    {
        return $this->phone_number; // must be in international format, e.g. 36201234567
    }
}
```

### 2. Create a notification

```php
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Notifications\SeeMeChannel;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification
{
    public function via(mixed $notifiable): array
    {
        return [SeeMeChannel::class];
    }

    public function toSeeMe(mixed $notifiable): SeeMeMessage
    {
        return SeeMeMessage::create("A rendelésed #{$this->order->id} feladásra került.")
            ->sender('WebShop');
    }
}
```

### 3. Send it

```php
$user->notify(new OrderShipped($order));
```

## Delivery Reports

SeeMe can call back your application with the delivery status of each sent SMS.

### Setup

1. Enable the webhook and configure the callback URL in SeeMe admin to point to your endpoint.
2. Set the callback IP from SeeMe admin ("Callback forrás") in your `.env`:

```env
SEEME_DELIVERY_REPORT_ENABLED=true
SEEME_DELIVERY_REPORT_PATH=seeme/delivery-report
SEEME_CALLBACK_IP=80.249.169.123
```

3. Listen for the event in your `EventServiceProvider`:

```php
use DoxaSoft\LaravelSeeMe\Events\DeliveryReportReceived;

protected $listen = [
    DeliveryReportReceived::class => [
        YourDeliveryReportListener::class,
    ],
];
```

### Event payload

```php
class DeliveryReportReceived
{
    public readonly ?string $reference;       // your reference ID from send()
    public readonly string  $number;          // recipient's phone number
    public readonly string  $sender;          // sender ID
    public readonly DeliveryStatus $status;   // enum: Delivered, Undeliverable, etc.
    public readonly string  $statusMessage;   // human-readable status text
    public readonly float   $price;           // charged amount
    public readonly string  $timestamp;       // e.g. "20240101120000"
    public readonly ?int    $mccmnc;          // MCC+MNC of recipient's network
    public readonly int     $split;           // number of SMS segments
}
```

### Delivery status codes

| Enum case | Code | Description | Terminal? |
|---|---|---|---|
| `TransmittedToSmsc` | 3 | Transmitted to remote SMSC | No |
| `AcceptedBySmsc` | 4 | Accepted by remote SMSC | No |
| `RejectedBySmsc` | 5 | Rejected by remote SMSC | Yes |
| `Delivered` | 6 | Delivered | Yes |
| `Undeliverable` | 7 | Undeliverable | Yes |
| `UndeliverableOtherError` | 1 | Undeliverable (other error) | Yes |
| `ValidityExpired` | 9 | Validity period expired | Yes |
| `StatusMissing` | 10 | Status missing | Yes |
| `SendingStopped` | 2 | Sending stopped _(inactive)_ | Yes |
| `WaitingForDevice` | 8 | Waiting for target device _(inactive)_ | No |

```php
use DoxaSoft\LaravelSeeMe\Enums\DeliveryStatus;

if ($event->status === DeliveryStatus::Delivered) {
    // mark order as confirmed
}

if ($event->status->isTerminal()) {
    // no further updates expected
}
```

## Inbound SMS

SeeMe can forward SMS messages received on a rented number to your application.

### Setup

```env
SEEME_INBOUND_SMS_ENABLED=true
SEEME_INBOUND_SMS_PATH=seeme/inbound
SEEME_CALLBACK_IP=80.249.169.123
```

Listen for the event:

```php
use DoxaSoft\LaravelSeeMe\Events\InboundSmsReceived;

protected $listen = [
    InboundSmsReceived::class => [
        YourInboundSmsListener::class,
    ],
];
```

### Event payload

```php
class InboundSmsReceived
{
    public readonly string $message;      // SMS text
    public readonly string $number;       // sender's phone number
    public readonly string $destination;  // your rented number that received the SMS
    public readonly string $timestamp;    // e.g. "20131001122341"
}
```

## Exception Handling

All API errors throw `DoxaSoft\LaravelSeeMe\Exceptions\SeeMeException`. The exception code matches the SeeMe API error code.

```php
use DoxaSoft\LaravelSeeMe\Exceptions\SeeMeException;

try {
    SeeMe::send('36201234567', SeeMeMessage::create('Hello'));
} catch (SeeMeException $e) {
    match ($e->getApiCode()) {
        4  => // Authentication error – check SEEME_API_KEY
        7  => // Insufficient balance
        13 => // IP not allowed – run SeeMe::setIp() or whitelist in admin
        18 => // Invalid API key format
        default => // see full error code list below
    };

    logger()->error('SeeMe error', [
        'code'    => $e->getApiCode(),
        'message' => $e->getMessage(),
    ]);
}
```

### API error codes

| Code | Description |
|---|---|
| 0 | Success |
| 1 | Missing parameter |
| 2 | Only numbers allowed in parameter |
| 3 | Number must be in international format |
| 4 | Authentication error |
| 5 | Profile settings incomplete |
| 6 | Message exceeds 459 characters |
| 7 | Insufficient balance |
| 8 | Gateway temporarily unavailable |
| 9 | Sender ID not allowed or contains illegal characters |
| 11 | Credit line insufficient |
| 12 | Message contains unsupported characters |
| 13 | IP address not allowed |
| 14 | IP address already allowed |
| 15 | Callback parameter malformed |
| 16 | Message length depends on character encoding |
| 17 | Callback URL unreachable |
| 18 | Invalid API key |

## Testing

Use `SeeMe::fake()` in your tests to prevent real HTTP requests to the SeeMe API.

```php
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\SeeMeFacade as SeeMe;

public function test_sends_sms_on_order_shipped(): void
{
    $fake = SeeMe::fake();

    // trigger your code that sends an SMS
    $this->post('/orders/1/ship');

    // assert an SMS was sent
    $fake->assertSent(function (SeeMeMessage $message, string $number) {
        return $number === '36201234567'
            && str_contains($message->getContent(), 'feladásra');
    });

    // or simpler assertions
    $fake->assertSentTo('36201234567');
    $fake->assertSentCount(1);
}

public function test_no_sms_sent_on_failed_payment(): void
{
    $fake = SeeMe::fake();

    $this->post('/orders/1/pay', ['card' => 'invalid']);

    $fake->assertNothingSent();
}
```

### Available assertions

| Method | Description |
|---|---|
| `assertSent(Closure $callback)` | Assert an SMS matching the callback was sent |
| `assertSentTo(string $number)` | Assert an SMS was sent to this number |
| `assertSentCount(int $count)` | Assert exactly N SMSes were sent |
| `assertNothingSent()` | Assert no SMSes were sent |

## License

MIT
