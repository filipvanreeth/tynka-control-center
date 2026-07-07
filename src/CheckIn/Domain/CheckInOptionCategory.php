<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\Common\Domain\TranslatedText;

final class CheckInOptionCategory
{
    private function __construct(
        private readonly CheckInOptionCategoryId $id,
        private readonly TranslatedText $title,
    ) {
    }

    public static function reconstitute(
        CheckInOptionCategoryId $id,
        TranslatedText $title,
    ): self {
        return new self($id, $title);
    }

    public function id(): CheckInOptionCategoryId
    {
        return $this->id;
    }

    public function title(): TranslatedText
    {
        return $this->title;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
