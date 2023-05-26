<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddYubikeyFields extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('yubikey_data');
        $table->addColumn('credential_id', 'string', ['limit' => 255])
              ->addColumn('rp_id', 'string', ['limit' => 255])
              ->update();
    }
}
