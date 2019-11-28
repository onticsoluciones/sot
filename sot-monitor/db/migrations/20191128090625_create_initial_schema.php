<?php

use Phinx\Migration\AbstractMigration;

class CreateInitialSchema extends AbstractMigration
{
    public function change()
    {
        $this->table('device')
            ->addColumn('entity_id', 'string')
            ->addColumn('name', 'text')
            ->addColumn('acknowledged', 'boolean')
            ->create()
            ;

        $this->table('event')
            ->addColumn('device_id', 'integer')
            ->addColumn('severity', 'integer')
            ->addColumn('message', 'string')
            ->addForeignKey('device_id', 'device', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION'
            ])
            ->create()
            ;
    }
}
