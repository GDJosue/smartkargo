<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialMigration extends AbstractMigration
{
    public function up(): void
    {
        // Table: users
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 50])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('full_name', 'string', ['limit' => 100])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['username'], ['unique' => true])
              ->create();

        // Table: tickets
        $tickets = $this->table('tickets');
        $tickets->addColumn('ticket_id', 'string', ['limit' => 20])
                ->addColumn('date_out', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('date_return', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('main_dest', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('passengers', 'text', ['null' => true]) // JSON string
                ->addColumn('res_code', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('dep_day', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('flight_num', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('duration', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('cabin', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('status', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('orig_code', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('orig_city', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('dest_code', 'string', ['limit' => 10, 'null' => true])
                ->addColumn('dest_city', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('orig_time', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('orig_term', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('dest_time', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('dest_term', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('aircraft', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('miles', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['ticket_id'], ['unique' => true])
                ->create();

        // Insert default admin user
        // password_hash('admin123', PASSWORD_BCRYPT)
        $this->execute("INSERT INTO users (username, password, full_name) VALUES ('admin', '$2y$10$AaGHA8acQVY3Y.krHnpZeO3SNSUNzDCk48Va68dPGYvBmuTkW.fei', 'Administrador Mas Cargo')");
    }

    public function down(): void
    {
        $this->table('tickets')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
