<?php

namespace App\Services;

use App\Models\ActivityLog;

class LogService
{
    /**
     * Record an activity log.
     *
     * @param string $type
     * @param string $action
     * @param string $description
     * @param array|null $payload
     * @param int|null $userId
     * @return ActivityLog
     */
    public static function record(string $type, string $action, string $description, ?array $payload = null, ?int $userId = null)
    {
        return ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
        ]);
    }
}
