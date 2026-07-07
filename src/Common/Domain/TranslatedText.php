<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

final readonly class TranslatedText
{
    /**
     * @param array<string, string> $translations
     * @throws InvalidTranslatedText
     */
    public function __construct(
        private array $translations,
    ) {
        if (empty($translations)) {
            throw InvalidTranslatedText::emptyTranslations();
        }

        foreach ($translations as $locale => $text) {
            new Locale($locale);

            if (trim($text) === '') {
                throw InvalidTranslatedText::emptyText($locale);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function translations(): array
    {
        return $this->translations;
    }

    public function hasLocale(Locale $locale): bool
    {
        return \array_key_exists($locale->value(), $this->translations);
    }

    public function forLocale(Locale $locale): string
    {
        if (!$this->hasLocale($locale)) {
            throw MissingTranslation::forLocale($locale);
        }

        return $this->translations[$locale->value()];
    }
}
