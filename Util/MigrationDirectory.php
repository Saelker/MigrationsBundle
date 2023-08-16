<?php

namespace Saelker\MigrationsBundle\Util;

class MigrationDirectory
{
	private readonly int $priority;

	public function __construct(private readonly string $directory,
								?int $priority = null)
	{
		$this->priority = $priority ?: 0;
	}

	public function getDirectory(): string
	{
		return $this->directory;
	}

	public function getPriority(): int
	{
		return $this->priority;
	}
}