<?php

declare(strict_types=1);

namespace NotificationChannels\RocketChat;

use GuzzleHttp\Client as HttpClient;

final class RocketChat
{
    /** @var \GuzzleHttp\Client */
    private $http;

    /** @var string */
    private $url;

    /** @var string */
    private $token;

    /**
     * @param  \GuzzleHttp\Client  $http
     * @param  string  $url
     * @param  string  $token
     * @param  string|null  $defaultChannel
     * @return void
     */
    public function __construct(HttpClient $http, string $url, string $token)
    {
        $this->http = $http;
        $this->url = rtrim($url, '/');
        $this->token = $token;
    }

    /**
     * Returns RocketChat token.
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Send a message.
     *
     * @param  array  $message
     * @return void
     */
    public function sendMessage(array $message): void
    {
        $url = sprintf('%s/hooks/%s', $this->url, $this->token);

        $this->post($url, [
            'json' => $message,
        ]);
    }

    /**
     * Perform a simple post request.
     *
     * @param  string  $url
     * @param  array  $options
     * @return void
     */
    private function post(string $url, array $options): void
    {
        $this->http->post($url, $options);
    }
}
