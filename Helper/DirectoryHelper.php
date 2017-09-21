<?php

namespace Saelker\MigrationsBundle\Helper;

class DirectoryHelper
{
	/**
	 * @var
	 */
	private $cleanDepth;

	/**
	 * @var
	 */
	private $directorySeparator;

	/**
	 * @var
	 */
	private $useCamelCase;

	/**
	 * DirectoryHelper constructor.
	 * @param $cleanDepth
	 * @param $directorySeparator
	 * @param $useCamelCase
	 */
	public function __construct($cleanDepth, $directorySeparator, $useCamelCase)
	{
		$this->cleanDepth = $cleanDepth;
		$this->directorySeparator = $directorySeparator;
		$this->useCamelCase = $useCamelCase;
	}

	/**
	 * @param $directory
	 * @return string
	 */
	public function getCleanedPath($directory)
	{
		if (!$this->cleanDepth) {
			return $directory;
		}

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
	 * @param array $directories
	 * @return array
	 */
	public function getSourceDirectories(array $directories)
	{
		$srcDirectories = [];

		foreach ($directories as $directory) {
			if (strpos($directory, 'src') !== false) {
				$srcDirectories[] = $directory;
			}
		}

		return $srcDirectories;
	}
}