<?php

declare(strict_types=1);

namespace App\Tests\Access\Domain;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\InvalidEmail;

final class EmailTest extends TestCase
{
    public function testNormalisesToLowercaseAndTrims(): void
    {
        self::assertSame('jan@tynka.be', Email::fromString('  Jan@Tynka.BE ')->toString());
    }

    public function testEqualityIsCaseInsensitive(): void
    {
        self::assertTrue(
            Email::fromString('Jan@x.be')->equals(Email::fromString('jan@x.be'))
        );
    }

    public function testRejectsInvalidFormat(): void
    {
        $this->expectException(InvalidEmail::class);

        Email::fromString('not-an-email');
    }
}
