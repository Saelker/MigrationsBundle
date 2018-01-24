<?php

namespace Saelker\MigrationsBundle\Util;

class MigrationDirectory
{
	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var integer
	 */
	private $priority;

	/**
	 * MigrationDirectory constructor.
	 *
	 * @param string $directory
	 * @param int $priority
	 */
	public function __construct(string $directory, ?int $priority = null)
	{
		$this->directory = $directory;
		$this->priority = $priority ?: 0;
	}

	/**
	 * @return string
	 */
	public function getDirectory(): string
	{
		return $this->directory;
	}

	/**
	 * @return int
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}
}