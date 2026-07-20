<?php

namespace App\Support;

class Tracking
{
    public static function attrs(string $event, array $params = []): string
    {
        $payload = e(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return 'data-track-event="'.e($event).'" data-track-params="'.$payload.'"';
    }
}
