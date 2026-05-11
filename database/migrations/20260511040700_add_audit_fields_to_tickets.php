<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAuditFieldsToTickets extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets');
        $table->addColumn('created_by_name', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('created_at_cdmx', 'datetime', ['null' => true])
              ->update();
    }
}
