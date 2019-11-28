<?php

use Phinx\Migration\AbstractMigration;

class CreateAlertTable extends AbstractMigration
{
    public function change()
    {
        $this->table('alert')
            ->addColumn('type', 'string')
            ->addColumn('data', 'string')
            ->addColumn('timestamp', 'biginteger')
            ->create();
    }
}
