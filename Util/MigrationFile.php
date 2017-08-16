<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

abstract class MigrationFile
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var SqlStatement[]
	 */
	private $sqlStatements = [];

	/**
	 * @var string[][]
	 */
	private $classes = [];

	/**
	 * @var Schema
	 */
	private $fromSchema;

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
	 * @param Schema $schema
	 * @return $this
	 */
	public function addSchema(Schema $schema)
	{
		$fromSchema = $this->getFromSchema();
		$platform = $this->em->getConnection()->getDatabasePlatform();

		foreach($fromSchema->getMigrateToSql($schema, $platform) as $sql) {
			$this->addSql($sql);
		}

		return $this;
	}

	/**
	 * @param $sql
	 * @param null $params
	 * @return $this
	 */
	public function addSql($sql, $params = null)
	{
		$this->sqlStatements[] = new SqlStatement($sql, $params);

		return $this;
	}

	/**
	 * @param $class
	 * @return $this
	 */
	public function addClass($class)
	{
		$this->classes[] = $class;

		return $this;
	}

	/**
	 * @return Schema
	 */
	public function getSchema()
	{
		return clone $this->getFromSchema();
	}

	/**
	 * @return Schema
	 */
	public function getFromSchema()
	{
		if (!$this->fromSchema) {
			$tool = new SchemaTool($this->em);
			$this->fromSchema = $tool->getSchemaFromMetadata($this->getTableMetadata());
		}

		return $this->fromSchema;
	}

	/**
	 * @return array
	 */
	public function getTableMetadata()
	{
		$meta = [];

		foreach($this->classes as $class) {
			$metaData = $this->em->getClassMetadata($class['class']);

			foreach ($class['ignoreColumns'] as $ignoreColumn) {
			    if (array_key_exists($ignoreColumn, $metaData->fieldMappings)) {
			        unset($metaData->fieldMappings[$ignoreColumn]);
                }
            }

            $meta[] = $metaData;
		}


		return $meta;
	}

	/**
	 * @return SqlStatement[]
	 */
	public function getSqlStatements()
	{
		return $this->sqlStatements;
	}

	/**
	 * @return $this
	 */
	private function executeSql()
	{
		foreach($this->getSqlStatements() as $key => $sql) {
			$stmt = $this->em->getConnection()->prepare($sql->getSql());
			$stmt->execute($sql->getParams());

			unset($this->sqlStatements[$key]);
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