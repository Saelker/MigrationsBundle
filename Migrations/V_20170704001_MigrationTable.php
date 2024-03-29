<?php

namespace Saelker\MigrationsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_20170704001_MigrationTable extends MigrationFile
{
	private const TABLE = "migration";

	public function up(): void
	{
		if (!$this->connectionHelper->tablesExists([self::TABLE])) {

			$schema = new Schema();

			$table = $schema->createTable(self::TABLE);

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