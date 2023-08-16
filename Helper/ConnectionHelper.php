<?php

namespace Saelker\MigrationsBundle\Helper;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;

class ConnectionHelper
{
	private AbstractSchemaManager $schemaManager;

	public function __construct(private EntityManagerInterface $em)
	{
		$this->schemaManager = $em->getConnection()->getSchemaManager();
	}

	public function resetEntityManager(): void
	{
		$this->em = $this->em->create(
			$this->em->getConnection(),
			$this->em->getConfiguration()
		);

		$this->em->clear();
	}

	public function tablesExists($tables): bool
	{
		return $this->schemaManager->tablesExist($tables);
	}

	public function hasColumn(string $table, string $column): bool
	{
		$tableColumns = $this->schemaManager->listTableColumns($table);

		foreach ($tableColumns as $name => $tableColumn) {
			if ($name === $column) {
				return true;
			}
		}

		return false;
	}
}
