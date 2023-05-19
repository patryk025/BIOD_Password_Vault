<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Vaults extends AbstractMigration
{
    public function change(): void
    {
        $users = $this->table('vaults');
        $users->addColumn('id_user', 'integer', ['null' => false, 'signed' => false])
              ->addColumn('url', 'string', ['null' => false])
              ->addColumn('login', 'string', ['null' => false])
              ->addColumn('password', 'string', ['null' => false])
              ->addColumn('created', 'datetime', ['null' => false])
              ->addColumn('changed', 'datetime')
              ->addIndex(['id_user'], ['unique' => true])
              ->addForeignKey('id_user', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}
