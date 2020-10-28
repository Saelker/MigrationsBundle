<?php

namespace Saelker\MigrationsBundle\Helper;

use Saelker\MigrationsBundle\Util\MigrationDirectory;

class DirectoryHelper
{
	/**
	 * @var string|null
	 */
	private $cleanDepth;

	/**
	 * @var string
	 */
	private $directorySeparator = '/';

	/**
	 * @var bool|null
	 */
	private $useCamelCase;

	/**
	 * DirectoryHelper constructor.
	 *
	 * @param $cleanDepth
	 * @param $useCamelCase
	 */
	public function __construct(?string $cleanDepth, ?bool $useCamelCase)
	{
		$this->cleanDepth = $cleanDepth;
		$this->useCamelCase = $useCamelCase;
	}

	/**
	 * @param string $directory
	 *
	 * @return string
	 */
	public function getCleanedPath(string $directory): string
	{
		if (!$this->cleanDepth) {
			return $directory;
		}

		// replace all black slashes with a normal slash
		$directory = str_replace('\\', '/', $directory);

		$directories = explode($this->directorySeparator, $directory);
		$directoriesCount = count($directories);

		$parts = [];

		for ($i = $directoriesCount; $i > $directoriesCount - $this->cleanDepth; $i--) {
			if ($this->useCamelCase) {
				$parts[] = ucwords(str_replace('-', ' ', $directories[$i - 1]), '');
			} else {
				$parts[] = $directories[$i - 1];
			}
		}

		return implode('/', array_reverse($parts));
	}

	/**
	 * @param MigrationDirectory[] $directories
	 *
	 * @return string[]
	 */
	public function getSourceDirectories(array $directories): array
	{
		$srcDirectories = [];

		/** @var MigrationDirectory $directory */
		foreach ($directories as $directory) {
			$directoryName = $directory->getDirectory();
			if (strpos($directoryName, 'src') !== false) {
				$srcDirectories[] = $directoryName;
			}
		}

		return $srcDirectories;
	}
}
