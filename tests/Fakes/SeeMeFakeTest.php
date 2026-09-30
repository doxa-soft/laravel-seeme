<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Fakes;

use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\SeeMeFacade as SeeMe;
use DoxaSoft\LaravelSeeMe\Support\SeeMeFake;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;
use PHPUnit\Framework\AssertionFailedError;

class SeeMeFakeTest extends TestCase
{
    public function test_fake_swaps_binding_and_returns_fake_instance(): void
    {
        $fake = SeeMe::fake();

        $this->assertInstanceOf(SeeMeFake::class, $fake);
    }

    public function test_fake_records_sent_messages(): void
    {
        $fake = SeeMe::fake();

        SeeMe::send('36201234567', SeeMeMessage::create('Hello'));

        $fake->assertSentCount(1);
    }

    public function test_assert_sent_with_closure(): void
    {
        $fake = SeeMe::fake();

        SeeMe::send('36201234567', SeeMeMessage::create('Order confirmed'));

        $fake->assertSent(fn (SeeMeMessage $msg, string $number) =>
            $number === '36201234567' && str_contains($msg->getContent(), 'Order')
        );
    }

    public function test_assert_sent_to_passes_for_correct_number(): void
    {
        $fake = SeeMe::fake();
        SeeMe::send('36201234567', SeeMeMessage::create('Hi'));

        $fake->assertSentTo('36201234567');
    }

    public function test_assert_sent_to_fails_for_wrong_number(): void
    {
        $fake = SeeMe::fake();
        SeeMe::send('36201234567', SeeMeMessage::create('Hi'));

        $this->expectException(AssertionFailedError::class);
        $fake->assertSentTo('36309999999');
    }

    public function test_assert_nothing_sent_passes_when_empty(): void
    {
        $fake = SeeMe::fake();
        $fake->assertNothingSent();
    }

    public function test_assert_nothing_sent_fails_after_send(): void
    {
        $fake = SeeMe::fake();
        SeeMe::send('36201234567', SeeMeMessage::create('Hi'));

        $this->expectException(AssertionFailedError::class);
        $fake->assertNothingSent();
    }

    public function test_assert_sent_count_passes_for_correct_count(): void
    {
        $fake = SeeMe::fake();
        SeeMe::send('36201234567', SeeMeMessage::create('First'));
        SeeMe::send('36209876543', SeeMeMessage::create('Second'));

        $fake->assertSentCount(2);
    }

    public function test_assert_sent_count_fails_for_wrong_count(): void
    {
        $fake = SeeMe::fake();
        SeeMe::send('36201234567', SeeMeMessage::create('Hi'));

        $this->expectException(AssertionFailedError::class);
        $fake->assertSentCount(3);
    }
}
