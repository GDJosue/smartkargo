<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDashboardAndFlight2Fields extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets');
        $table->addColumn('carrier2', 'string', ['limit' => 10, 'null' => true])
              ->addColumn('flight2', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('from2', 'string', ['limit' => 10, 'null' => true])
              ->addColumn('to2', 'string', ['limit' => 10, 'null' => true])
              ->addColumn('time2', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('requested_by', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('signature', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('on_file', 'boolean', ['default' => false, 'null' => true])
              ->update();
    }
}
