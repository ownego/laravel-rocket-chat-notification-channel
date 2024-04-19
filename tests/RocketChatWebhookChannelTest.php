<?php

declare(strict_types=1);

namespace NotificationChannels\RocketChat\Test;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NotificationChannels\RocketChat\Exceptions\CouldNotSendNotification;
use NotificationChannels\RocketChat\RocketChat;
use NotificationChannels\RocketChat\RocketChatMessage;
use NotificationChannels\RocketChat\RocketChatWebhookChannel;
use PHPUnit\Framework\TestCase;

final class RocketChatWebhookChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_can_send_a_notification(): void
    {
        $client = Mockery::mock(GuzzleHttpClient::class);

        $apiBaseUrl = 'http://localhost:3000';
        $token = ':token';

        $client->shouldReceive('post')->once()
            ->with(
                "{$apiBaseUrl}/hooks/{$token}",
                [
                    'json' => [
                        'text' => 'hello',
                    ],
                ]
            )->andReturn(new Response(200));

        $rocketChat = new RocketChat($client, $apiBaseUrl, $token, $channel);
        $channel = new RocketChatWebhookChannel($rocketChat);
        $channel->send(new TestNotifiable(), new TestNotification());
    }

    public function test_it_handles_generic_errors(): void
    {
        $client = Mockery::mock(GuzzleHttpClient::class);
        $this->expectException(CouldNotSendNotification::class);

        $apiBaseUrl = 'http://localhost:3000';
        $token = ':token';

        $client->shouldReceive('post')->once()
            ->with(
                "{$apiBaseUrl}/hooks/{$token}",
                [
                    'json' => [
                        'text' => 'hello',
                    ],
                ]
            )->andThrow(new \Exception('Test'));

        $rocketChat = new RocketChat($client, $apiBaseUrl, $token, $channel);
        $channel = new RocketChatWebhookChannel($rocketChat);
        $channel->send(new TestNotifiable(), new TestNotification());
    }
}

class TestNotifiable
{
    use Notifiable;
}

class TestNotification extends Notification
{
    public function toRocketChat(): RocketChatMessage
    {
        return RocketChatMessage::create('hello');
    }
}

class TestNotificationWithMissedChannel extends Notification
{
    public function toRocketChat(): RocketChatMessage
    {
        return RocketChatMessage::create('hello')->from(':token');
    }
}
