<?php

namespace DoxaSoft\LaravelSeeMe\Tests\Unit;

use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Tests\TestCase;

class SeeMeMessageTest extends TestCase
{
    public function test_create_sets_content(): void
    {
        $msg = SeeMeMessage::create('Hello world');

        $this->assertEquals('Hello world', $msg->getContent());
        $this->assertNull($msg->getSender());
        $this->assertNull($msg->getReference());
        $this->assertNull($msg->getCallbackParams());
        $this->assertNull($msg->getCallbackUrl());
    }

    public function test_fluent_builder_chains(): void
    {
        $msg = SeeMeMessage::create('Hello')
            ->sender('MySender')
            ->reference('ref-123')
            ->withAllCallbacks()
            ->callbackUrl('https://example.com/cb');

        $this->assertEquals('MySender', $msg->getSender());
        $this->assertEquals('ref-123', $msg->getReference());
        $this->assertEquals('1,2,3,4,5,6,7,8,9,10', $msg->getCallbackParams());
        $this->assertEquals('https://example.com/cb', $msg->getCallbackUrl());
    }

    public function test_with_callbacks_custom_params(): void
    {
        $msg = SeeMeMessage::create('Hello')->withCallbacks('3,6,7');

        $this->assertEquals('3,6,7', $msg->getCallbackParams());
    }

    public function test_builder_is_immutable(): void
    {
        $original = SeeMeMessage::create('Hello');
        $withSender = $original->sender('Test');

        $this->assertNull($original->getSender());
        $this->assertEquals('Test', $withSender->getSender());
        $this->assertNotSame($original, $withSender);
    }
}
