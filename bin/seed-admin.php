<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TynkaControlCenter\Access\Application\RegisterAccount;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\User\Domain\UserId;
use TynkaControlCenter\User\Domain\UserRepository;

/*
 * Wegwerp-seed: maakt het EERSTE admin-account. Zodra user-beheer volstaat
 * (bin/create-account.php of de admin-sectie), verdwijnt dit. De creatie loopt
 * door RegisterAccount — dezelfde codepad als de CLI — zodat hashing en
 * invarianten hier niet apart worden herhaald. Dit script houdt enkel z'n
 * idempotente 'bestaat al → skip' + de handler-check.
 */

/** @var ContainerInterface $container */
$container = require dirname(__DIR__) . '/config/bootstrap.php';

$email = $_ENV['SEED_ADMIN_EMAIL'] ?? 'admin@tynka.be';
$password = $_ENV['SEED_ADMIN_PASSWORD'] ?? 'change-me';
$userId = $_ENV['SEED_ADMIN_HANDLER_ID'] ?? 'filip';

/** @var AccountRepository $accounts */
$accounts = $container->get(AccountRepository::class);
/** @var UserRepository $handlers */
$handlers = $container->get(UserRepository::class);

if ($accounts->byEmail(Email::fromString($email)) !== null) {
    echo "Account for {$email} already exists — skipping.", PHP_EOL;

    return;
}

if ($handlers->byId(UserId::fromString($userId)) === null) {
    fwrite(STDERR, "Unknown handler '{$userId}' — cannot link account.\n");

    exit(1);
}

/** @var RegisterAccount $registerAccount */
$registerAccount = $container->get(RegisterAccount::class);
$registerAccount($email, $password, Role::Admin, UserId::fromString($userId));

echo 'Created admin account:', PHP_EOL;
echo "  email:    {$email}", PHP_EOL;
echo "  password: {$password}", PHP_EOL;
echo "  handler:  {$userId}", PHP_EOL;
