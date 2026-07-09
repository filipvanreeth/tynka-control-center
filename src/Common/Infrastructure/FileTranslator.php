<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Infrastructure;

use TynkaControlCenter\Common\Domain\Translator;

final class FileTranslator implements Translator
{
    /**
     * @var array<string, mixed>
     */
    private array $catalogueCache = [];

    public function __construct(
        private readonly string $languagePath,
        private readonly string $defaultLocale = 'en',
        private readonly string $fallbackLocale = 'en',
    ) {
    }

    public function translate(string $key, ?string $locale = null): string
    {
        $activeLocale = $locale ?? $this->defaultLocale;

        $translation = $this->resolve($this->loadCatalogue($activeLocale), $key);

        if ($translation !== null) {
            return $translation;
        }

        $fallbackTranslation = $this->resolve($this->loadCatalogue($this->fallbackLocale), $key);

        return $fallbackTranslation ?? $key;
    }

    /**
     * Summary of loadCatalogue
     * @param string $locale
     * @return array<string, mixed>
     */
    private function loadCatalogue(string $locale): array
    {
        if (\array_key_exists($locale, $this->catalogueCache)) {
            return $this->catalogueCache[$locale];
        }

        $path = "$this->languagePath/{$locale}.php";

        if (!file_exists($path)) {
            $this->catalogueCache[$locale] = [];

            return $this->catalogueCache[$locale];
        }

        $catalogue = include $path;

        $this->catalogueCache[$locale] = \is_array($catalogue) ? $catalogue : [];

        return $this->catalogueCache[$locale];
    }

    /**
     * @param array<string, mixed> $catalogue The catalogue with translations.
     * @param string $key Key to check.
     * @return string|null Returns the value as a string or null if no string.
     */
    private function resolve(array $catalogue, string $key): ?string
    {
        $segments = explode('.', $key);
        $value = $catalogue;

        foreach ($segments as $segment) {
            if (!\is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return \is_string($value) ? $value : null;
    }
}
