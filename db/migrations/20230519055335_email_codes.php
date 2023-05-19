<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailCodes extends AbstractMigration
{
    public function change(): void
    {
        $users = $this->table('email_codes');
        $users->addColumn('id_user', 'integer', ['null' => false, 'signed' => false])
              ->addColumn('identifier', 'string', ['null' => false])
              ->addColumn('valid_code', 'integer', ['null' => false])
              ->addColumn('valid_from', 'datetime', ['null' => false])
              ->addColumn('valid_to', 'datetime', ['null' => false])
              ->addForeignKey('id_user', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}
