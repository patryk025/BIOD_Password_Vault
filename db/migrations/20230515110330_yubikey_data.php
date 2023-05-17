<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class YubikeyData extends AbstractMigration
{
    public function change(): void
    {
        $users = $this->table('yubikey_data');
        $users->addColumn('id_user', 'integer', ['null' => false, 'signed' => false])
              ->addColumn('credential_public_key', 'string')
              ->addColumn('certificate', 'string')
              ->addColumn('certificate_issuer', 'string')
              ->addColumn('certificate_subject', 'string')
              ->addColumn('created', 'datetime')
              ->addIndex(['id_user'], ['unique' => true])
              ->addForeignKey('id_user', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}
