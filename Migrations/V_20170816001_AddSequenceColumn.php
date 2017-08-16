<?php

namespace Saelker\MigrationsBundle\Migrations;

use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_20170816001_AddSequenceColumn extends MigrationFile
{
    public function preUp()
    {
        $this->addClass(Migration::class);
    }

    public function up()
    {
        $schema = $this->getSchema();

        $table = $schema->getTable('migration');
        $table->addColumn('sequence', 'integer', ['notnull' => false]);

        $this->addSchema($schema);
    }
}