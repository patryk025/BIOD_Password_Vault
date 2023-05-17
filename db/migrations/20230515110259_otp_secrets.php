<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OtpSecrets extends AbstractMigration
{
    public function change(): void
    {
        $users = $this->table('otp_secrets');
        $users->addColumn('id_user', 'integer', ['null' => false, 'signed' => false])
              ->addColumn('encrypted_secret', 'string')
              ->addColumn('created', 'datetime')
              ->addIndex(['id_user'], ['unique' => true])
              ->addForeignKey('id_user', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}
