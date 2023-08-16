<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Saelker\MigrationsBundle\Helper\ConnectionHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportFile implements \Stringable
{
	private ?object $instance = null;

	public function __construct(private readonly SplFileInfo             $file,
								private readonly ?KernelInterface        $kernel,
								private readonly ?EntityManagerInterface $em,
								private readonly ?ContainerInterface     $container,
								private readonly ?ConnectionHelper        $connectionHelper)
	{
	}

	public function migrate(): static
	{
		$instance = $this->getInstance();
		$instance->executeUp();

		return $this;
	}

	public function getInstance(): MigrationFile
	{
		if (!$this->instance) {
			$class = $this->getNamespace() . "\\" . $this->getClassName();

			$this->instance = new $class($this->kernel, $this->em, $this->container, $this->connectionHelper);
		}

		return $this->instance;
	}

	private function getNamespace(): ?string
	{
		$pattern = "/namespace (.*);/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	private function getClassName(): ?string
	{
		$pattern = "/class (\w*)/";
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	public function rollback(): ImportFile
	{
		$instance = $this->getInstance();
		$instance->down();

		return $this;
	}

	public function getNote(): ?string
	{
		$pattern = '/const NOTE = "(.*)";/';
		preg_match($pattern, $this->file->getContents(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

	public function getFileIdentifier(): ?string
	{
		preg_match('/V_(\d*)_.*/', $this->file->getBasename(), $hits);

		return !empty($hits) ? $hits[1] : null;
	}

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