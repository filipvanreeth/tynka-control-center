<?php
declare(strict_types=1);

namespace TynkaControlCenter\Presentation;

use TynkaControlCenter\Config\AppConfig;

final class ViewRenderer
{
    public function __construct(
        private readonly AppConfig $appConfig
    ) {
    }
    
    public function render(string $path, array $data = []): bool|string
    {
        ob_start();
        extract($data);
        include BASE_PATH . '/resources/views/' . $path . '.php';

        return ob_get_clean();
    }
}