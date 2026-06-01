<?php

namespace App\Events;

class SecurityEvent
{
    public const LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    public const LOGIN_FAILURE = 'LOGIN_FAILURE';
    public const ACCESS_DENIED = 'ACCESS_DENIED';
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const RATE_LIMIT_TRIGGERED = 'RATE_LIMIT_TRIGGERED';

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $type,
        public readonly array $context = []
    ) {
    }
}
