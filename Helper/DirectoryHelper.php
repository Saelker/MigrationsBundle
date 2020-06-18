<?php

namespace Saelker\MigrationsBundle\Helper;

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
	 * @param array $directories
	 *
	 * @return array
	 */
	public function getSourceDirectories(array $directories): array
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