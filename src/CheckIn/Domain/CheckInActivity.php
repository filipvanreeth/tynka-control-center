<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\Common\Domain\TranslatedText;

final class CheckInActivity
{
    private function __construct(
        private readonly CheckInActivityId $id,
        private readonly TranslatedText $title,
        private readonly CheckInActivityCategoryId $categoryId,
    ) {
    }

    public static function reconstitute(
        CheckInActivityId $id,
        TranslatedText $title,
        CheckInActivityCategoryId $categoryId,
    ): self {
        return new self($id, $title, $categoryId);
    }

    public function id(): CheckInActivityId
    {
        return $this->id;
    }

    public function title(): TranslatedText
    {
        return $this->title;
    }

    public function categoryId(): CheckInActivityCategoryId
    {
        return $this->categoryId;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
