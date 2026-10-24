<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;

class NotificationService
{
    /**
     * Notify specific users by their IDs.
     */
    public static function notifyUsers(
        array $userIds,
        string $type,
        string $title,
        string $body,
        string $icon = 'fa-solid fa-bell',
        string $color = 'primary',
        ?string $link = null,
        ?array $data = null
    ): void {
        foreach ($userIds as $userId) {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                'color' => $color,
                'link' => $link,
                'data' => $data,
            ]);

            event(new NotificationCreated($notification));
        }
    }

    /**
     * Notify all users with a specific role.
     */
    public static function notifyRole(
        string $role,
        string $type,
        string $title,
        string $body,
        string $icon = 'fa-solid fa-bell',
        string $color = 'primary',
        ?string $link = null,
        ?array $data = null
    ): void {
        $notification = Notification::create([
            'role' => $role,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'color' => $color,
            'link' => $link,
            'data' => $data,
        ]);

        event(new NotificationCreated($notification));
    }

    /**
     * Notify multiple roles at once.
     */
    public static function notifyRoles(
        array $roles,
        string $type,
        string $title,
        string $body,
        string $icon = 'fa-solid fa-bell',
        string $color = 'primary',
        ?string $link = null,
        ?array $data = null
    ): void {
        foreach ($roles as $role) {
            $notification = Notification::create([
                'role' => $role,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                'color' => $color,
                'link' => $link,
                'data' => $data,
            ]);

            event(new NotificationCreated($notification));
        }
    }
}
