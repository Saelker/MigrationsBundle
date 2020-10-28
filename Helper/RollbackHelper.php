<?php

namespace Saelker\MigrationsBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Repository\MigrationRepository;
use Saelker\MigrationsBundle\Util\ImportFile;
use Saelker\MigrationsBundle\Util\MigrationDirectory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class RollbackHelper
{
	/**
	 * @var MigrationRepository
	 */
	private $repository;

	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * RollbackHelper constructor.
	 *
	 * @param MigrationRepository $migrationRepository
	 * @param EntityManagerInterface $em
	 * @param ContainerInterface $container
	 */
	public function __construct(MigrationRepository $migrationRepository,
								EntityManagerInterface $em,
								ContainerInterface $container)
	{
		$this->repository = $migrationRepository;
		$this->em = $em;
		$this->container = $container;
	}

	/**
	 * @param int $sequence
	 * @param MigrationDirectory[] $directories
	 *
	 * @return ImportFile[]
	 *
	 * @throws \Exception
	 */
	public function getSequenceImportFiles(int $sequence, array $directories): array
	{
		/** @var ImportFile[] $rollbackFiles */
		$rollbackFiles = [];

		/** @var Migration[] $migrations */
		$migrations = $this
			->repository
			->getQueryBuilder()
			->andWhere('m.sequence = :sequenceFilter')
			->setParameter('sequenceFilter', $sequence)
			->orderBy('m.identifier', 'DESC')
			->getQuery()
			->getResult();

		foreach ($migrations as $migration) {
			// 1. Try to calc the directory
			$useDirectory = null;
			foreach ($directories as $directory) {
				if (strpos($directory->getDirectory(), $migration->getDirectory())) {
					$useDirectory = $directory->getDirectory();
					break;
				}
			}

			if (!$useDirectory) {
				throw new \Exception('Could not find directory for migration director: ' . $migration->getDirectory());
			}

			$finder = new Finder();
			$finder->files()->name('V_' . $migration->getIdentifier() . '*');

			foreach ($finder->in($useDirectory) as $file) {
				$rollbackFiles[] = new ImportFile($file, $this->em, $this->container);
			}
		}

		return $rollbackFiles;
	}
}
