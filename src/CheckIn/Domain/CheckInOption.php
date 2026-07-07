<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\Common\Domain\TranslatedText;

final class CheckInOption
{
    private function __construct(
        private readonly CheckInOptionId $id,
        private readonly TranslatedText $title,
        private readonly CheckInOptionCategoryId $categoryId,
    ) {
    }

    public static function reconstitute(
        CheckInOptionId $id,
        TranslatedText $title,
        CheckInOptionCategoryId $categoryId,
    ): self {
        return new self($id, $title, $categoryId);
    }

    public function id(): CheckInOptionId
    {
        return $this->id;
    }

    public function title(): TranslatedText
    {
        return $this->title;
    }

    public function categoryId(): CheckInOptionCategoryId
    {
        return $this->categoryId;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
