<?php

namespace Saelker\MigrationsBundle\Helper;

use Saelker\MigrationsBundle\Util\ImportFile;

class DependencyHelper
{
	/**
	 * @var array
	 */
	private $dependencyResolutions = [];

	/**
	 * @var array
	 */
	private $availableImportFiles = [];

	/**
	 * @var array
	 */
	private $newOrderedImportFiles = [];

	/**
	 * @param array $importFiles
	 * @return array
	 */
	public function resolveDependencies(array $importFiles): array
	{
		// Clear Old Data
		$this->clear();

		/** @var ImportFile $importFile */
		foreach ($importFiles as $importFile) {
			$migrationFile = $importFile->getInstance();
			$this->availableImportFiles[$migrationFile->getClassName()] = $importFile;

			if ($resolution = $migrationFile->getDependencyResolution()) {
				$this->dependencyResolutions[$resolution][] = $importFile;
			}
		}

		foreach ($importFiles as $importFile) {
			$migrationFile = $importFile->getInstance();

			if (($dependency = $migrationFile->getDependency()) && array_key_exists($dependency, $this->dependencyResolutions)) {
				// Loop through all Dependencies
				/** @var ImportFile $dependencyResolution */
				foreach ($this->dependencyResolutions[$dependency] as $dependencyResolution) {
					$this->newOrderedImportFiles[] = $dependencyResolution;
					unset($this->availableImportFiles[$dependencyResolution->getInstance()->getClassName()]);
				}

				unset($this->dependencyResolutions[$dependency]);
			}

			if (array_key_exists($migrationFile->getClassName(), $this->availableImportFiles)) {
				$this->newOrderedImportFiles[] = $importFile;
				unset($this->availableImportFiles[$migrationFile->getClassName()]);
			}
		}

		return $this->newOrderedImportFiles;
	}

	/**
	 *
	 */
	private function clear(): void
	{
		$this->dependencyResolutions = [];
		$this->availableImportFiles = [];
		$this->newOrderedImportFiles = [];
	}

}