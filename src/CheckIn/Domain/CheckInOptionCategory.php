<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryId;
use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class CheckInOptionCategory
{
    public function __construct(
        private TranslatedText $title,
        private Slug $slug,
    ) {
    }
    
    public function title(): TranslatedText
    {
        return $this->title;
    }
    
    public function slug(): Slug
    {
        return $this->slug;
    }
}

