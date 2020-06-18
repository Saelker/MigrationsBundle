<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ImportFile
 *
 * @package Saelker\MigrationsBundle\Util
 */
class ImportFile
{
	/**
	 * @var SplFileInfo
	 */
	private $file;

	/**
	 * @var MigrationFile
	 */
	private $instance;

	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * ImportFile constructor.
	 *
	 * @param SplFileInfo $file
	 * @param EntityManagerInterface $entityManager
	 * @param ContainerInterface $container
	 */
	public function __construct(SplFileInfo $file, ?EntityManagerInterface $entityManager, ?ContainerInterface $container)
	{
		$this->file = $file;
		$this->em = $entityManager;
		$this->container = $container;
	}

	/**
	 * @return ImportFile
	 *
	 * @throws \Exception
	 *
	 * @throws \Throwable
	 */
	public function migrate(): ImportFile
	{
		$instance = $this->getInstance();
		$instance->executeUp();

		return $this;
	}

	/**
	 * @return MigrationFile
	 */
	public function getInstance(): MigrationFile
	{
		if (!$this->instance) {
			$class = $this->getNamespace() . "\\" . $this->getClassName();

			$this->instance = new $class($this->em, $this->container);
		}

		return $this->instance;
	}

	/**
	 * @return string|null
	 */
	private function getNamespace(): ?string
	{
		$pattern = "/namespace (.*);/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	/**
	 * @return string|null
	 */
	private function getClassName(): ?string
	{
		$pattern = "/class (\w*)/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	/**
	 * @return ImportFile
	 */
	public function rollback(): ImportFile
	{
		$instance = $this->getInstance();
		$instance->down();

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getNote(): ?string
	{
		$pattern = '/const NOTE = "(.*)";/';
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	/**
	 * @return string|null
	 */
	public function getFileIdentifier(): ?string
	{
		preg_match('/V_(\d*)_.*/', $this->file->getBasename(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getFile()->getBasename();
	}

	/**
	 * @return SplFileInfo
	 */
	public function getFile()
	{
		return $this->file;
	}

}