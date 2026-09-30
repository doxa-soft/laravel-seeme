<?php

namespace DoxaSoft\LaravelSeeMe;

use DoxaSoft\LaravelSeeMe\Contracts\SeeMeServiceInterface;
use DoxaSoft\LaravelSeeMe\Data\SeeMeBalance;
use DoxaSoft\LaravelSeeMe\Data\SeeMeMessage;
use DoxaSoft\LaravelSeeMe\Data\SeeMeResult;
use DoxaSoft\LaravelSeeMe\Exceptions\SeeMeException;
use Illuminate\Http\Client\Factory as HttpFactory;

class SeeMeService implements SeeMeServiceInterface
{
    private const API_VERSION = '2.0.1';
    private const CHECKSUM_LENGTH = 4;

    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $defaultSender,
    ) {
        if ($apiKey !== '') {
            $this->validateApiKey($apiKey);
        }
    }

    public function send(string $number, SeeMeMessage $message): SeeMeResult
    {
        $params = [
            'number'  => $number,
            'message' => $message->getContent(),
        ];

        $sender = $message->getSender() ?? ($this->defaultSender !== '' ? $this->defaultSender : null);
        if ($sender !== null) {
            $params['sender'] = $sender;
        }

        if ($ref = $message->getReference()) {
            $params['reference'] = $ref;
        }

        if ($cb = $message->getCallbackParams()) {
            $params['callback'] = $cb;
        }

        if ($url = $message->getCallbackUrl()) {
            $params['callbackurl'] = $url;
        }

        return SeeMeResult::fromArray($this->call($params));
    }

    public function balance(): SeeMeBalance
    {
        return SeeMeBalance::fromArray($this->call(['method' => 'balance']));
    }

    public function setIp(string $ip): SeeMeResult
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            throw new SeeMeException('Invalid IP address format', 15);
        }

        return SeeMeResult::fromArray($this->call(['method' => 'setip', 'ip' => $ip]));
    }

    private function call(array $params): array
    {
        $params['key']        = $this->apiKey;
        $params['format']     = 'json';
        $params['apiVersion'] = self::API_VERSION;

        $data = $this->http->get($this->baseUrl, $params)->json();

        if (($data['result'] ?? null) === 'ERR') {
            throw new SeeMeException(
                $data['message'] ?? 'Unknown error',
                (int) ($data['code'] ?? 0),
            );
        }

        return $data;
    }

    private function validateApiKey(string $key): void
    {
        $hash     = substr($key, 0, -self::CHECKSUM_LENGTH);
        $checksum = substr($key, -self::CHECKSUM_LENGTH);

        if (substr(md5($hash), 0, self::CHECKSUM_LENGTH) !== $checksum) {
            throw new SeeMeException('Invalid API key', 18);
        }
    }
}
