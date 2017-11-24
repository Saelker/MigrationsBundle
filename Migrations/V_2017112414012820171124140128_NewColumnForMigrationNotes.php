<?php

namespace Saelker\MigrationsBundle\Migrations;

use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_2017112414012820171124140128_NewColumnForMigrationNotes extends MigrationFile
{
	const TABLE = 'migration';
	const COLUMN = 'note';

	public function preUp()
	{
		$this->addClass([
			'class' => Migration::class,
			'ignoreColumns' => [self::COLUMN],
		]);
	}

	public function up()
	{
		if ($this->connectionHelper->tablesExists(self::TABLE) &&
			$this->connectionHelper->hasColumn(self::TABLE, self::COLUMN))
		{
			return;
		}

		$schema = $this->getSchema();
		$table = $schema->getTable(self::TABLE);

		$table->addColumn(self::COLUMN, 'string',['notnull' => false]);

		$this->addSchema($schema);
	}
}