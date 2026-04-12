<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateAuthSystem extends AbstractMigration
{
    public function up(): void
    {
        // Add email column to users
        $this->table('users')
             ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'after' => 'username'])
             ->addIndex(['email'], ['unique' => true])
             ->update();

        // Create login_codes table
        $this->table('login_codes')
             ->addColumn('user_id', 'integer', ['signed' => false])
             ->addColumn('code', 'string', ['limit' => 6])
             ->addColumn('expires_at', 'datetime')
             ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
             ->create();

        // Update existing admin user with a default email
        $this->execute("UPDATE users SET email = 'admin@mascargo.com' WHERE username = 'admin'");
    }

    public function down(): void
    {
        $this->table('login_codes')->drop()->save();
        $this->table('users')
             ->removeIndex(['email'])
             ->removeColumn('email')
             ->update();
    }
}
