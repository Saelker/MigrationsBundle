<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Saelker\MigrationsBundle\Helper\ConnectionHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class MigrationFile
{
	/**
	 * @var EntityManagerInterface
	 */
	protected $em;
	/**
	 * @var ContainerInterface
	 */
	protected $container;
	/**
	 * @var ConnectionHelper
	 */
	protected $connectionHelper;
	/**
	 * @var string|null
	 */
	protected $dependency;
	/**
	 * @var string|null
	 */
	protected $dependencyResolution;
	/**
	 * @var int
	 */
	protected $migrationOrder = 0;
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
	 *
	 * @param EntityManagerInterface $em
	 * @param ContainerInterface $container
	 */
	public function __construct(EntityManagerInterface $em, ContainerInterface $container)
	{
		$this->em = $em;
		$this->container = $container;
		$this->connectionHelper = $container->get(ConnectionHelper::class);

		$this->init();
	}

	/**
	 *
	 */
	public function init(): void
	{
	}

	/**
	 * @return MigrationFile
	 *
	 * @throws \Exception
	 *
	 * @throws \Throwable
	 */
	public function executeUp(): MigrationFile
	{
		// Execute all statements in transaction
		$this->em->getConnection()->transactional(function () {
			$this->connectionHelper->resetEntityManager();

			$this->preUp();

			$this->up();

			$this->executeSql();
			$this->postUp();
		});

		return $this;
	}

	/**
	 *
	 */
	public function preUp()
	{
	}

	/**
	 *
	 */
	abstract public function up();

	/**
	 * @return MigrationFile
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 */
	private function executeSql(): MigrationFile
	{
		foreach ($this->getSqlStatements() as $key => $sql) {
			$stmt = $this->em->getConnection()->prepare($sql->getSql());
			$stmt->execute($sql->getParams());

			unset($this->sqlStatements[$key]);
		}

		return $this;
	}

	/**
	 * @return SqlStatement[]
	 */
	public function getSqlStatements(): array
	{
		return $this->sqlStatements;
	}

	/**
	 *
	 */
	public function postUp()
	{
	}

	/**
	 * @param Schema $schema
	 *
	 * @return MigrationFile
	 *
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function addSchema(Schema $schema): MigrationFile
	{
		$fromSchema = $this->getFromSchema();
		$platform = $this->em->getConnection()->getDatabasePlatform();

		foreach ($fromSchema->getMigrateToSql($schema, $platform) as $sql) {
			$this->addSql($sql);
		}

		return $this;
	}

	/**
	 * @return Schema
	 *
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function getFromSchema(): Schema
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
	public function getTableMetadata(): array
	{
		$meta = [];

		foreach ($this->classes as $class) {
			$metaData = clone $this->em->getClassMetadata($class['class']);

			foreach ($class['ignoreColumns'] as $ignoreColumn) {
				if (array_key_exists($ignoreColumn, $metaData->fieldMappings)) {
					unset($metaData->fieldMappings[$ignoreColumn]);
				}

				if (array_key_exists($ignoreColumn, $metaData->associationMappings)) {
					unset($metaData->associationMappings[$ignoreColumn]);
				}
			}

			$meta[] = $metaData;
		}


		return $meta;
	}

	/**
	 * @param $sql
	 * @param null $params
	 *
	 * @return MigrationFile
	 */
	public function addSql($sql, $params = null): MigrationFile
	{
		$this->sqlStatements[] = new SqlStatement($sql, $params);

		return $this;
	}

	/**
	 * @param $class
	 *
	 * @return MigrationFile
	 */
	public function addClass($class): MigrationFile
	{
		$this->classes[] = $class;

		return $this;
	}

	/**
	 * @return Schema
	 *
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function getSchema(): Schema
	{
		return clone $this->getFromSchema();
	}

	/**
	 * @return null|string
	 */
	public function getDependency(): ?string
	{
		return $this->dependency;
	}

	/**
	 * @param null|string $dependency
	 *
	 * @return MigrationFile
	 */
	public function setDependency(?string $dependency): MigrationFile
	{
		$this->dependency = $dependency;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getDependencyResolution(): ?string
	{
		return $this->dependencyResolution;
	}

	/**
	 * @param null|string $dependencyResolution
	 *
	 * @return MigrationFile
	 */
	public function setDependencyResolution(?string $dependencyResolution): MigrationFile
	{
		$this->dependencyResolution = $dependencyResolution;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMigrationOrder(): int
	{
		return $this->migrationOrder;
	}

	/**
	 * @param int $migrationOrder
	 *
	 * @return MigrationFile
	 */
	public function setMigrationOrder(int $migrationOrder): MigrationFile
	{
		$this->migrationOrder = $migrationOrder;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return static::class;
	}

	/**
	 * Will execute the down function for given migrations
	 */
	public function down(): void
	{

	}
}
