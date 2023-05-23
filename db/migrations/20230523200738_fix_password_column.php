<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixPasswordColumn extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        $table->changeColumn('password', 'string', ['limit' => 255])
              ->update();
    }
}
