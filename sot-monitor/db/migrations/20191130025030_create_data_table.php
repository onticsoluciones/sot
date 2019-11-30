<?php

use Phinx\Migration\AbstractMigration;

class CreateDataTable extends AbstractMigration
{
    public function change()
    {
        $this->table('data')
            ->addColumn('key', 'string')
            ->addColumn('value', 'string')
            ->create();
    }
}
