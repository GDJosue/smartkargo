<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateTicketFields extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('tickets');
        $table->renameColumn('main_dest', 'approved_by')
              ->renameColumn('res_code', 'guide_code')
              ->renameColumn('dep_day', 'area')
              ->renameColumn('flight_num', 'transportadora')
              ->renameColumn('duration', 'priority')
              ->renameColumn('orig_time', 'time')
              ->renameColumn('dest_time', 'flight')
              ->removeColumn('cabin')
              ->removeColumn('orig_term')
              ->removeColumn('dest_term')
              ->update();
    }

    public function down(): void
    {
        $table = $this->table('tickets');
        $table->renameColumn('approved_by', 'main_dest')
              ->renameColumn('guide_code', 'res_code')
              ->renameColumn('area', 'dep_day')
              ->renameColumn('transportadora', 'flight_num')
              ->renameColumn('priority', 'duration')
              ->renameColumn('time', 'orig_time')
              ->renameColumn('flight', 'dest_time')
              ->addColumn('cabin', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('orig_term', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('dest_term', 'string', ['limit' => 50, 'null' => true])
              ->update();
    }
}
