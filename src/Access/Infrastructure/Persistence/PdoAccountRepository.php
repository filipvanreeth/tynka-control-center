<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Infrastructure\Persistence;

use PDO;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\AccountId;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\User\Domain\UserId;

/**
 * Pdo-adapter voor de {@see AccountRepository}-poort. Vertaalt het domein ↔ de
 * `accounts`-kolommen; `uuid` draagt de app-identiteit, de `id`-kolom is enkel
 * surrogaatsleutel. Zelfde vorm als PdoCheckInRepository.
 */
final class PdoAccountRepository implements AccountRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName,
    ) {
    }

    public function save(Account $account): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->tableName} (uuid, email, password_hash, role, handler_id)
             VALUES (:uuid, :email, :password_hash, :role, :handler_id)"
        );

        $executed = $stmt->execute([
            ':uuid' => $account->id()->toString(),
            ':email' => $account->email()->toString(),
            ':password_hash' => $account->passwordHash()->toString(),
            ':role' => $account->role()->value,
            ':handler_id' => $account->userId()->toString(),
        ]);

        if (!$executed) {
            throw new \RuntimeException(
                'Failed to store account: ' . implode(', ', $stmt->errorInfo())
            );
        }
    }

    public function byId(AccountId $id): ?Account
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tableName} WHERE uuid = :uuid"
        );
        $stmt->execute([':uuid' => $id->toString()]);

        return $this->hydrate($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function byEmail(Email $email): ?Account
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tableName} WHERE email = :email"
        );
        $stmt->execute([':email' => $email->toString()]);

        return $this->hydrate($stmt->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * @param array<string, mixed>|false $row
     */
    private function hydrate(array|false $row): ?Account
    {
        if ($row === false) {
            return null;
        }

        return Account::reconstitute(
            id: AccountId::fromString((string) $row['uuid']),
            email: Email::fromString((string) $row['email']),
            passwordHash: PasswordHash::fromHash((string) $row['password_hash']),
            role: Role::from((string) $row['role']),
            userId: UserId::fromString((string) $row['handler_id']),
        );
    }
}
