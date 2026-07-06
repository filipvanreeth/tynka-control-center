<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

final class TranslatedText
{
    /**
     * Summary of __construct
     * @param array<string, string> $translations
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private array $translations,
    ) {
        if (empty($translations)) {
            throw new \InvalidArgumentException("Translations are empty.");
        }

        foreach ($translations as $locale => $text) {
            new Locale($locale);

            if (empty(trim($text))) {
                throw new \InvalidArgumentException("Text cannot be empty");
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

    public function forLocale(Locale $locale): string
    {
        return $this->translations[$locale->value()];
    }
}