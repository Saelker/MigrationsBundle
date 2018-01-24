<?php

namespace Saelker\MigrationsBundle\Helper;

use Saelker\MigrationsBundle\Util\ImportFile;

class DependencyHelper
{

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
		}

		foreach ($importFiles as $importFile) {
			$migrationFile = $importFile->getInstance();

			if (($dependency = $migrationFile->getDependency()) && array_key_exists($dependency, $this->availableImportFiles)) {
				$this->newOrderedImportFiles[] = $this->availableImportFiles[$dependency];
				unset($this->availableImportFiles[$dependency]);
			}

			$this->newOrderedImportFiles[] = $importFile;
			unset($this->availableImportFiles[$migrationFile->getClassName()]);
		}


		return $this->newOrderedImportFiles;
	}

}