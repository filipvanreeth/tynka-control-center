<?php

declare(strict_types=1);

namespace App\Tests\Access\Domain;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Domain\PasswordHash;

final class PasswordHashTest extends TestCase
{
    public function testVerifiesTheCorrectPassword(): void
    {
        $hash = PasswordHash::fromPlainText('correct horse');

        self::assertTrue($hash->verify('correct horse'));
        self::assertFalse($hash->verify('battery staple'));
    }

    public function testNeverStoresThePlainText(): void
    {
        $hash = PasswordHash::fromPlainText('s3cret');

        self::assertStringNotContainsString('s3cret', $hash->toString());
    }

    public function testReconstitutesFromAStoredHash(): void
    {
        $stored = PasswordHash::fromPlainText('s3cret')->toString();

        self::assertTrue(PasswordHash::fromHash($stored)->verify('s3cret'));
    }
}
