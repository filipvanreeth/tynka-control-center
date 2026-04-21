<?php
declare(strict_types=1);

namespace TynkaControlCenter\Core;

use RuntimeException;

final class Vite
{
    public function __construct(
        private readonly string $publicPath,
        private readonly bool $isDevelopment,
        private readonly string $developmentServerUrl = 'http://localhost:5173',
    ) {
    }

    public function assets(string $entry): string
    {
        return $this->isDevelopment
            ? $this->developmentAssets($entry)
            : $this->productionAssets($entry);
    }

    private function developmentAssets(string $entry): string
    {
        $developmentServerUrl = rtrim($this->developmentServerUrl, '/');

        $entry = ltrim($entry, '/');

        return implode("\n", [
            sprintf('<script type="module" src="%s/@vite/client"></script>', $developmentServerUrl),
            sprintf('<script type="module" src="%s/%s"></script>', $developmentServerUrl, $entry),
        ]);
    }

    private function productionAssets(string $entry): string
    {
        $manifestPath = "{$this->publicPath}/build/.vite/manifest.json";

        if (!is_file($manifestPath)) {
            throw new RuntimeException('Vite manifest not found.');
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (!is_array($manifest)) {
            throw new RuntimeException('Invalid Vite manifest.');
        }

        if (!isset($manifest[$entry])) {
            throw new RuntimeException(sprintf('Entry "%s" not found in Vite manifest.', $entry));
        }

        $asset = $manifest[$entry];
        $tags = [];

        foreach ($asset['css'] ?? [] as $cssFile) {
            $tags[] = sprintf(
                '<link rel="stylesheet" href="https://tynka-control-center.lndo.site/build/%s">',
                ltrim($cssFile, '/')
            );
        }

        $tags[] = sprintf(
            '<script type="module" src="https://tynka-control-center.lndo.site/build/%s"></script>',
            ltrim($asset['file'], '/')
        );

        return implode("\n", $tags);
    }
}