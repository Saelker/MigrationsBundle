<?php

namespace Saelker\MigrationsBundle\Util;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\SplFileInfo;

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
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * ImportFile constructor.
	 * @param SplFileInfo $file
	 * @param EntityManager $entityManager
	 * @param ContainerInterface $container
	 */
	public function __construct(SplFileInfo $file, ?EntityManager $entityManager, ?ContainerInterface $container)
	{
		$this->file = $file;
		$this->em = $entityManager;
		$this->container = $container;
	}

	/**
	 * @return SplFileInfo
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return $this
	 */
	public function migrate()
	{
		$instance = $this->getInstance();
		$instance->executeUp();

		return $this;
	}

	/**
	 * @return MigrationFile
	 */
	private function getInstance()
	{
		if (!$this->instance) {
			$class = $this->getNamespace() . "\\" . $this->getClassName();

			$this->instance = new $class($this->em, $this->container);
		}

		return $this->instance;
	}

	/**
	 * @return string|bool
	 */
	private function getNamespace()
	{
		$pattern = "/namespace (.*);/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : false;
	}

	/**
	 * @return string|bool
	 */
	private function getClassName()
	{
		$pattern = "/class (\w*)/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : false;
	}

	/**
	 * @return string
	 */
	public function getFileIdentifier()
	{
		preg_match('/V_(\d*)_.*/', $this->file->getBasename(), $hits);

		return !empty($hits) ? $hits[1] : false;
	}

	public function __toString()
	{
		return $this->getFile()->getBasename();
	}

}