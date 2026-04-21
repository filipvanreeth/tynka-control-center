<?php
declare(strict_types=1);

namespace TynkaControlCenter\Enums;

use TynkaControlCenter\Services\TranslationService;

enum CheckInOptions: string
{
    case Peed = 'peed';
    case Pooped = 'pooped';
    case Food = 'food';
    case Snack = 'snack';

    public function translationKey(): string
    {
        return "check_in_options.{$this->value}";
    }

    public function getType(): ?string
    {
        return match ($this) {
            self::Peed, self::Pooped => 'walk',
            self::Food, self::Snack => 'care',
        };
    }

    public static function byType(string $type): array
    {
        return array_filter(
            self::cases(),
            fn(self $option) => $option->getType() === $type
        );
    }

    public static function forForm(TranslationService $translationService, ?string $locale = null): array
    {
        return array_map(
            static fn(self $option): array => [
                'value' => $option->value,
                'label' => $translationService->translate($option->translationKey(), $locale),
            ],
            self::cases()
        );
    }
}
