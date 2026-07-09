<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TynkaControlCenter\Access\Application\RegisterAccount;
use TynkaControlCenter\Access\Domain\EmailAlreadyRegistered;
use TynkaControlCenter\Access\Domain\InvalidEmail;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\User\Domain\UserId;
use TynkaControlCenter\User\Domain\UserRepository;

/*
 * CLI om een account met een rol aan te maken:
 *
 *   php bin/create-account.php <email> <password> <role> <userId>
 *
 * De daadwerkelijke creatie loopt door de RegisterAccount-service (uniek e-mail +
 * hashing via het domein). Dit script doet enkel de I/O en de cross-context
 * handler-bestaat-check — de orkestratie van twee contexten hoort op dit
 * entrypoint, niet in de Access-service zelf.
 */

/** @var ContainerInterface $container */
$container = require dirname(__DIR__) . '/config/bootstrap.php';

$email = $argv[1] ?? null;
$password = $argv[2] ?? null;
$roleArg = $argv[3] ?? null;
$userId = $argv[4] ?? null;

if ($email === null || $password === null || $roleArg === null || $userId === null) {
    fwrite(STDERR, "Usage: php bin/create-account.php <email> <password> <role> <userId>\n");
    fwrite(STDERR, '       role is one of: ' . implode(', ', array_column(Role::cases(), 'value')) . "\n");

    exit(1);
}

$role = Role::tryFrom($roleArg);

if ($role === null) {
    fwrite(STDERR, "Unknown role '{$roleArg}' — expected one of: " . implode(', ', array_column(Role::cases(), 'value')) . "\n");

    exit(1);
}

/** @var UserRepository $handlers */
$handlers = $container->get(UserRepository::class);

if ($handlers->byId(UserId::fromString($userId)) === null) {
    fwrite(STDERR, "Unknown handler '{$userId}' — cannot link account.\n");

    exit(1);
}

/** @var RegisterAccount $registerAccount */
$registerAccount = $container->get(RegisterAccount::class);

try {
    $registerAccount($email, $password, $role, UserId::fromString($userId));
} catch (InvalidEmail | EmailAlreadyRegistered $e) {
    fwrite(STDERR, $e->getMessage() . "\n");

    exit(1);
}

echo 'Created account:', PHP_EOL;
echo "  email:    {$email}", PHP_EOL;
echo "  role:     {$role->value}", PHP_EOL;
echo "  handler:  {$userId}", PHP_EOL;
