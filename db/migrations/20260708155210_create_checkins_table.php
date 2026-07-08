<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCheckinsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('checkins')
            ->addColumn('uuid', 'string', ['null' => false])
            ->addColumn('handler', 'string', ['null' => false])
            ->addColumn('peed', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('pooped', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('food', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('snack', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex('uuid', ['unique' => true])
            ->create();
    }
}
