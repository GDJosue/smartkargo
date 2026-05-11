<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIsAdminToUsers extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('users');
        $table->addColumn('is_admin', 'boolean', ['default' => false])
              ->update();

        // Make the first user an admin by default
        $this->execute('UPDATE users SET is_admin = 1 ORDER BY id ASC LIMIT 1');
    }

    public function down(): void
    {
        $table = $this->table('users');
        $table->removeColumn('is_admin')
              ->update();
    }
}
