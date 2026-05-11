<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPassengerType extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets');
        $table->addColumn('passenger_type', 'string', ['limit' => 255, 'null' => true])
              ->update();
    }
}
