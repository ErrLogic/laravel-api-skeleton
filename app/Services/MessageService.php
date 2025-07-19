<?php

namespace App\Services;

use App\Events\BroadcastMessageEvent;
use App\Events\PrivateMessageEvent;

class MessageService
{
    public function broadcastMessage(string $message): array
    {
        broadcast(new BroadcastMessageEvent($message));

        return [
            'content' => $message,
        ];
    }

    public function privateMessage(string $userId, string $message): array
    {
        broadcast(new PrivateMessageEvent($userId, $message));

        return [
            'user_id' => $userId,
            'content' => $message,
        ];
    }
}
