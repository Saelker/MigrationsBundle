<?php

namespace Saelker\MigrationsBundle\Helper;

class DirectoryHelper
{
	private $cleanDepth;

	/**
	 * DirectoryHelper constructor.
	 * @param $cleanDepth
	 */
	public function __construct($cleanDepth)
	{
		$this->cleanDepth = $cleanDepth;
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

		$directories = explode('/', $directory);
		$directoriesCount = count($directories);

		$parts = [];

		for($i = $directoriesCount; $i > $directoriesCount - $this->cleanDepth; $i--) {
			$parts[] = $directories[$i - 1];
		}

		return implode('/', array_reverse($parts));
	}
}