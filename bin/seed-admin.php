<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Handler\Domain\HandlerId;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

/*
 * Wegwerp-seed: maakt het EERSTE admin-account. Zodra de admin-sectie accounts
 * kan aanmaken (of het console-luik er is), verdwijnt dit. Bewust app-side —
 * door het domein (Account::register + PasswordHash), niet via een raw insert —
 * zodat wachtwoord-hashing en invarianten gerespecteerd blijven.
 */

/** @var ContainerInterface $container */
$container = require dirname(__DIR__) . '/config/bootstrap.php';

$email = $_ENV['SEED_ADMIN_EMAIL'] ?? 'admin@tynka.be';
$password = $_ENV['SEED_ADMIN_PASSWORD'] ?? 'change-me';
$handlerId = $_ENV['SEED_ADMIN_HANDLER_ID'] ?? 'filip';

/** @var AccountRepository $accounts */
$accounts = $container->get(AccountRepository::class);
/** @var HandlerRepository $handlers */
$handlers = $container->get(HandlerRepository::class);

$emailVo = Email::fromString($email);

if ($accounts->byEmail($emailVo) !== null) {
    echo "Account for {$email} already exists — skipping.", PHP_EOL;

    return;
}

if ($handlers->byId(HandlerId::fromString($handlerId)) === null) {
    fwrite(STDERR, "Unknown handler '{$handlerId}' — cannot link account.\n");

    exit(1);
}

$accounts->save(Account::register(
    $emailVo,
    PasswordHash::fromPlainText($password),
    Role::Admin,
    HandlerId::fromString($handlerId),
));

echo 'Created admin account:', PHP_EOL;
echo "  email:    {$email}", PHP_EOL;
echo "  password: {$password}", PHP_EOL;
echo "  handler:  {$handlerId}", PHP_EOL;
