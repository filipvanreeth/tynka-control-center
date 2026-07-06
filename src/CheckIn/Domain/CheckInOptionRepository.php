<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\CheckIn\Domain\CheckInOption;
use TynkaControlCenter\Common\Domain\Slug;

interface CheckInOptionRepository
{
    /**
     * @return list<CheckInOption>
     */
    public function findAll(): array;
    public function findBySlug(Slug $slug): ?CheckInOption;
}
