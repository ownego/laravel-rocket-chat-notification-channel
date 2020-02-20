<?php

declare(strict_types=1);

namespace NotificationChannels\RocketChat\Test;

use Illuminate\Notifications\Notifiable;
use Mockery;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Notifications\Notification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use NotificationChannels\RocketChat\RocketChat;
use NotificationChannels\RocketChat\RocketChatMessage;
use NotificationChannels\RocketChat\RocketChatWebhookChannel;
use NotificationChannels\RocketChat\Exceptions\CouldNotSendNotification;
use PHPUnit\Framework\TestCase;

final class RocketChatWebhookChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function it_can_send_a_notification(): void
    {
        $client = Mockery::mock(Client::class);

        $apiBaseUrl = 'http://localhost:3000';
        $token = ':token';
        $channel = ':channel';

        $client->shouldReceive('post')->once()
            ->with(
                "{$apiBaseUrl}/hooks/{$token}",
                [
                    'json' => [
                        'text' => 'hello',
                        'channel' => $channel,
                    ],
                ]
            )->andReturn(new Response(200));

        $rocketChat = new RocketChat($client, $apiBaseUrl, $token, $channel);
        $channel = new RocketChatWebhookChannel($rocketChat);
        $channel->send(new TestNotifiable(), new TestNotification());
    }

    /** @test */
    public function it_does_not_send_a_message_when_channel_missed(): void
    {
        $this->expectException(CouldNotSendNotification::class);

        $rocketChat = new RocketChat(new Client(), '', '', '');
        $channel = new RocketChatWebhookChannel($rocketChat);
        $channel->send(new TestNotifiable(), new TestNotificationWithMissedChannel());
    }

    /** @test */
    public function it_does_not_send_a_message_when_from_missed(): void
    {
        $this->expectException(CouldNotSendNotification::class);

        $rocketChat = new RocketChat(new Client(), '', '', '');
        $channel = new RocketChatWebhookChannel($rocketChat);
        $channel->send(new TestNotifiable(), new TestNotificationWithMissedFrom());
    }
}

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForRocketChat(): string
    {
        return '';
    }
}

class TestNotification extends Notification
{
    public function toRocketChat(): RocketChatMessage
    {
        return RocketChatMessage::create('hello')->from(':token')->to(':channel');
    }
}

class TestNotificationWithMissedChannel extends Notification
{
    public function toRocketChat(): RocketChatMessage
    {
        return RocketChatMessage::create('hello')->from(':token');
    }
}

class TestNotificationWithMissedFrom extends Notification
{
    public function toRocketChat(): RocketChatMessage
    {
        return RocketChatMessage::create('hello')->to(':channel');
    }
}