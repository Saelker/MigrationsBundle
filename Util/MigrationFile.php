<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\ORM\EntityManager;

abstract class MigrationFile
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var SqlStatement[]
	 */
	private $sql = [];

	/**
	 * MigrationFile constructor.
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @return MigrationFile
	 */
	public function executeUp() {
		$this->preUp();

		$this->up();
		$this->executeSql();

		$this->postUp();

		return $this;
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return $this
	 */
	public function addSql($sql, $params = null)
	{
		$this->sql[] = new SqlStatement($sql, $params);

		return $this;
	}

	/**
	 * @return $this
	 */
	private function executeSql()
	{
		foreach($this->sql as $key => $sql) {
			$stmt = $this->em->getConnection()->prepare($sql->getSql());
			$stmt->execute($sql->getParams());

			unset($this->sql[$key]);
		}

		return $this;
	}

	/**
	 * @return mixed
	 */
	abstract public function up();

	/**
	 *
	 */
	public function preUp() {}

	/**
	 *
	 */
	public function postUp() {}
}