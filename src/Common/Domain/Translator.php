<?php
declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

interface Translator
{
    public function translate(string $key, ?string $locale = null): string;
}
