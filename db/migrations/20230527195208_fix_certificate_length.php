<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixCertificateLength extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('yubikey_data');
        $table->changeColumn('certificate', 'string', ['limit' => 1100])
              ->update();
    }
}
