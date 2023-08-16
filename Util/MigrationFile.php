<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Saelker\MigrationsBundle\Helper\ConnectionHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class MigrationFile
{
	protected EntityManagerInterface $em;

	protected ContainerInterface $container;

	protected ConnectionHelper $connectionHelper;

	protected ?string $dependency = null;

	protected ?string $dependencyResolution = null;

	protected int $migrationOrder = 0;

	/**
	 * @var SqlStatement[]
	 */
	private array $sqlStatements = [];

	/**
	 * @var string[][]
	 */
	private array $classes = [];

	private ?Schema $fromSchema = null;

	protected KernelInterface $kernel;

	public function __construct(KernelInterface        $kernel,
								EntityManagerInterface $em,
								ContainerInterface     $container,
								ConnectionHelper       $connectionHelper)
	{
		$this->em = $em;
		$this->container = $container;
		$this->kernel = $kernel;
		$this->connectionHelper = $connectionHelper;

		$this->init();
	}

	public function init(): void
	{
	}

	public function executeUp(): static
	{
		// Execute all statements in transaction
		$this->em->getConnection()->transactional(function (): void {
			$this->connectionHelper->resetEntityManager();

			$this->preUp();

			$this->up();

			$this->executeSql();
			$this->postUp();
		});

		return $this;
	}

	public function preUp(): void
	{
	}

	abstract public function up();

	private function executeSql(): static
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

	public function postUp(): void
	{
	}

	public function addSchema(Schema $schema): MigrationFile
	{
		$fromSchema = $this->getFromSchema();
		$platform = $this->em->getConnection()->getDatabasePlatform();

		foreach ($fromSchema->getMigrateToSql($schema, $platform) as $sql) {
			$this->addSql($sql);
		}

		return $this;
	}

	public function getFromSchema(): Schema
	{
		if (!$this->fromSchema) {
			$tool = new SchemaTool($this->em);
			$this->fromSchema = $tool->getSchemaFromMetadata($this->getTableMetadata());
		}

		return $this->fromSchema;
	}

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

	public function addSql($sql, $params = null): static
	{
		$this->sqlStatements[] = new SqlStatement($sql, $params);

		return $this;
	}

	public function addClass($class): static
	{
		$this->classes[] = $class;

		return $this;
	}

	public function getSchema(): Schema
	{
		return clone $this->getFromSchema();
	}

	public function getDependency(): ?string
	{
		return $this->dependency;
	}

	public function setDependency(?string $dependency): static
	{
		$this->dependency = $dependency;

		return $this;
	}

	public function getDependencyResolution(): ?string
	{
		return $this->dependencyResolution;
	}

	public function setDependencyResolution(?string $dependencyResolution): static
	{
		$this->dependencyResolution = $dependencyResolution;

		return $this;
	}

	public function getMigrationOrder(): int
	{
		return $this->migrationOrder;
	}

	public function setMigrationOrder(int $migrationOrder): MigrationFile
	{
		$this->migrationOrder = $migrationOrder;

		return $this;
	}

	public function getClassName(): string
	{
		return static::class;
	}

	public function down(): void
	{

	}
}