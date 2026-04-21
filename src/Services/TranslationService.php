<?php
declare(strict_types=1);

namespace TynkaControlCenter\Services;

final class TranslationService
{
    private array $catalogueCache = [];

    public function __construct(
        private readonly string $defaultLocale = 'en',
        private readonly string $fallbackLocale = 'en'
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

    public function getLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getHtmlLang(?string $locale = null): string
    {
        $activeLocale = $locale ?? $this->defaultLocale;

        return str_replace('_', '-', $activeLocale);
    }

    private function loadCatalogue(string $locale): array
    {
        if (array_key_exists($locale, $this->catalogueCache)) {
            return $this->catalogueCache[$locale];
        }

        $path = dirname(__DIR__, 2) . "/resources/lang/{$locale}.php";

        if (!file_exists($path)) {
            $this->catalogueCache[$locale] = [];

            return $this->catalogueCache[$locale];
        }

        $catalogue = require $path;

        $this->catalogueCache[$locale] = is_array($catalogue) ? $catalogue : [];

        return $this->catalogueCache[$locale];
    }

    private function resolve(array $catalogue, string $key): ?string
    {
        $segments = explode('.', $key);
        $value = $catalogue;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return is_string($value) ? $value : null;
    }
}
