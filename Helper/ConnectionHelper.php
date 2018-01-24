<?php

namespace Saelker\MigrationsBundle\Helper;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class ConnectionHelper
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var AbstractSchemaManager
	 */
	private $schemaManager;

	/**
	 * ConnectionHelper constructor.
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->em = $entityManager;
		$this->schemaManager = $entityManager->getConnection()->getSchemaManager();
	}

	/**
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function resetEntityManager(): void
	{
		$this->em = $this->em->create(
			$this->em->getConnection(),
			$this->em->getConfiguration()
		);

		$this->em->clear();
	}

	/**
	 * @param $tables
	 *
	 * @return bool
	 */
	public function tablesExists($tables): bool
	{
		return $this->schemaManager->tablesExist($tables);
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return bool
	 */
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
