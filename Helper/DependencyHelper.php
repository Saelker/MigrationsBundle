<?php

namespace Saelker\MigrationsBundle\Helper;

use Saelker\MigrationsBundle\Util\ImportFile;

class DependencyHelper
{
	/**
	 * array
	 */
	private $parentImportFiles = [];

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
		/** @var ImportFile $importFile */
		foreach ($importFiles as $importFile) {
			$migrationFile = $importFile->getInstance();

			$this->availableImportFiles[$migrationFile->getClassName()] = $importFile;

			if (!$migrationFile->getDependency()) {
				$this->parentImportFiles[$migrationFile->getClassName()] = $importFile;
			}
		}

		/**
		 * @var string $className
		 * @var ImportFile $importFile
		 */
		foreach ($this->parentImportFiles as $className => $importFile) {
			$this->handleImportFile($importFile);
		}

		return $this->newOrderedImportFiles;
	}

	/**
	 * @param ImportFile $importFile
	 */
	private function handleImportFile(ImportFile $importFile): void
	{
		$migrationFile = $importFile->getInstance();

		if (array_key_exists($migrationFile->getClassName(), $this->availableImportFiles)) {
			unset($this->availableImportFiles[$migrationFile->getClassName()]);
			$this->newOrderedImportFiles[] = $importFile;

			// Find Same Dependency Resolutions
			$sameDependencyResolutions = array_filter($this->availableImportFiles, function (ImportFile $importFile) use ($migrationFile) {
				return $importFile->getInstance()->getDependencyResolution() == $migrationFile->getDependencyResolution();
			});

			foreach ($sameDependencyResolutions as $sameDependencyResolution) {
				$this->handleImportFile($sameDependencyResolution);
			}

			// Find Depended Migrations
			$dependedMigrations = array_filter($this->availableImportFiles, function (ImportFile $importFile) use ($migrationFile) {
				return $importFile->getInstance()->getDependency() == $migrationFile->getDependencyResolution();
			});

			foreach ($dependedMigrations as $dependedMigration) {
				$this->handleImportFile($dependedMigration);
			}
		}
	}

}