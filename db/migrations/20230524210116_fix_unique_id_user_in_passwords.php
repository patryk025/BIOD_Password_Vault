<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixUniqueIdUserInPasswords extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('passwords');
        $table->dropForeignKey('id_user');
        $table->removeIndex(['id_user'], ['unique' => true]);
        $table->addForeignKey('id_user', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE']);
        $table->update();
    }
}
