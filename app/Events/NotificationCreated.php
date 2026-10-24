<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Notification $notification
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->notification->user_id) {
            $channels[] = new Channel("user.{$this->notification->user_id}");
        }

        if ($this->notification->role) {
            $channels[] = new Channel("role.{$this->notification->role}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'icon' => $this->notification->icon,
            'color' => $this->notification->color,
            'link' => $this->notification->link,
            'createdAt' => $this->notification->created_at->diffForHumans(),
            'readAt' => $this->notification->read_at,
        ];
    }
}
