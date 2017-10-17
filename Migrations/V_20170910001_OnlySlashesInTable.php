<?php

namespace Saelker\MigrationsBundle\Migrations;

use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\MigrationFile;

class V_20170910001_OnlySlashesInTable extends MigrationFile
{
	public function up()
	{
		$migrations = $this->em->getRepository(Migration::class)->findAll();

		/** @var Migration $migration */
		foreach ($migrations as $migration) {
			// Search for backslashes and replace them with a normal slash
			$migration->setDirectory(str_replace('\\', '/', $migration->getDirectory()));
		}

		$this->em->flush();
	}
}