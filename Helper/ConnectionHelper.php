<?php

namespace Saelker\MigrationsBundle\Helper;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManager;

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
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->schemaManager = $entityManager->getConnection()->getSchemaManager();
	}

	/**
	 *
	 */
	public function resetEntityManager()
	{
		$this->em = $this->em->create(
			$this->em->getConnection(),
			$this->em->getConfiguration()
		);

		$this->em->clear();
	}

	/**
	 * @param $tableName
	 * @return bool
	 */
	public function tablesExists($tables)
	{
		return $this->schemaManager->tablesExist($tables);
	}

	public function hasColumn($table, $column) {
		$tableColumns = $this->schemaManager->listTableColumns($table);

		foreach ($tableColumns as $name => $tableColumn) {
			if ($name === $column) {
				return true;
			}
		}

		return false;
	}
}