<?php

namespace App\Helpers;

use App\Models\AppNotification;

class NotificationHelper
{
    /**
     * Save a notification to the database.
     *
     * @param int $senderId
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array|null $data
     * @return AppNotification
     */
    public static function saveNotification($senderId, $userId, $title, $body, $data = null)
    {
        return AppNotification::create([
            'sender_id' => $senderId,
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'additional_data' => $data ? $data : [],
            'is_read' => false,
        ]);
    }
} 