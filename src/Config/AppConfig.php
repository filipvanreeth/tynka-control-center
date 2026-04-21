<?php
declare(strict_types=1);

namespace TynkaControlCenter\Config;

final class AppConfig
{
    public function __construct(
        public readonly string $appUrl,
        public readonly string $appVersion,
        public readonly string $dbDriver,
        public readonly string $dbDatabase,
        public readonly string $locale = 'en'
    ) {
    }
}