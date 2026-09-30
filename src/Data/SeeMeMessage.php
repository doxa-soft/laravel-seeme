<?php

namespace DoxaSoft\LaravelSeeMe\Data;

class SeeMeMessage
{
    private ?string $sender = null;
    private ?string $reference = null;
    private ?string $callbackParams = null;
    private ?string $callbackUrl = null;

    private function __construct(private readonly string $content) {}

    public static function create(string $content): self
    {
        return new self($content);
    }

    public function sender(string $sender): self
    {
        $clone = clone $this;
        $clone->sender = $sender;

        return $clone;
    }

    public function reference(string $reference): self
    {
        $clone = clone $this;
        $clone->reference = $reference;

        return $clone;
    }

    public function withCallbacks(string $params): self
    {
        $clone = clone $this;
        $clone->callbackParams = $params;

        return $clone;
    }

    public function withAllCallbacks(): self
    {
        return $this->withCallbacks('1,2,3,4,5,6,7,8,9,10');
    }

    public function callbackUrl(string $url): self
    {
        $clone = clone $this;
        $clone->callbackUrl = $url;

        return $clone;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getCallbackParams(): ?string
    {
        return $this->callbackParams;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }
}
