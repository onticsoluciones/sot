<?php

use Phinx\Migration\AbstractMigration;

class AddPriorityColumnToAlertTable extends AbstractMigration
{
    public function change()
    {
        $this->table('alert')
            ->addColumn('priority', 'integer', [
                'default' => 0
            ])
            ->update();
    }
}
