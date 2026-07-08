<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

enum Role: string
{
    case Handler = 'handler';
    case Admin = 'admin';

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
