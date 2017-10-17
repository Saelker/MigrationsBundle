<?php

namespace Saelker\MigrationsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_20170704001_MigrationTable extends MigrationFile
{
	private $table = 'migration';

	public function up()
	{
		if (!$this->connectionHelper->tablesExists($this->table)) {

			$schema = new Schema();

			$table = $schema->createTable($this->table);

			$table->addColumn('id', 'integer', ['autoincrement' => true]);
			$table->addColumn('identifier', 'string');
			$table->addColumn('directory', 'string');
			$table->addColumn('createdAt', 'datetime');
			$table->addColumn('sequence', 'integer');

			$table->setPrimaryKey(['id']);
			$table->addIndex(['directory'], 'directory_index');

			$this->addSchema($schema);
		}
	}
}