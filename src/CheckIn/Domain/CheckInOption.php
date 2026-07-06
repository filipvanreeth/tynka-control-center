<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class CheckInOption
{
    /**
     * Summary of __construct
     * @param TranslatedText $title
     * @param Slug $slug
     * @param Slug $categoryId
     */
    public function __construct(
        private TranslatedText $title,
        private Slug $slug,
        private Slug $categoryId,
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
    
    public function categoryId(): Slug
    {
        return $this->categoryId;
    }
}
