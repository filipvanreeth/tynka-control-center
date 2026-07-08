<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAccountsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('accounts')
            ->addColumn('uuid', 'string', ['null' => false])
            ->addColumn('email', 'string', ['null' => false])
            ->addColumn('password_hash', 'string', ['null' => false])
            ->addColumn('role', 'string', ['null' => false])
            ->addColumn('handler_id', 'string', ['null' => false])
            ->addIndex('uuid', ['unique' => true])
            ->addIndex('email', ['unique' => true])
            ->create();
    }
}
