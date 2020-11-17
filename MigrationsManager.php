<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManagerInterface;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Helper\DependencyHelper;
use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\Helper\RollbackHelper;
use Saelker\MigrationsBundle\Repository\MigrationRepository;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Saelker\MigrationsBundle\Util\ImportFile;
use Saelker\MigrationsBundle\Util\MigrationDirectory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class MigrationsManager
{
	/**
	 * To check if symfony is loaded in migration mode
	 *
	 * @var bool
	 */
	public static $migration = false;
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	/**
	 * @var MigrationDirectory[]
	 */
	private $directories;
	/**
	 * @var ContainerInterface
	 */
	private $container;
	/**
	 * @var DependencyHelper
	 */
	private $dependencyHelper;
	/**
	 * @var DirectoryHelper
	 */
	private $directoryHelper;
	/**
	 * @var bool
	 */
	private $scopeDirectories;
	/**
	 * @var RollbackHelper
	 */
	private $rollbackHelper;

	/**
	 * @var MigrationRepository
	 */
	private $migrationRepository;

	/**
	 * MigrationsManager constructor.
	 *
	 * @param EntityManagerInterface $em
	 * @param KernelInterface $kernel
	 * @param DependencyHelper $dependencyHelper
	 * @param DirectoryHelper $directoryHelper
	 * @param RollbackHelper $rollbackHelper
	 * @param MigrationRepository $migrationRepository
	 */
	public function __construct(EntityManagerInterface $em,
								KernelInterface $kernel,
								DependencyHelper $dependencyHelper,
								DirectoryHelper $directoryHelper,
								RollbackHelper $rollbackHelper,
								MigrationRepository $migrationRepository)
	{
		$this->em = $em;
		$this->container = $kernel->getContainer();
		$this->dependencyHelper = $dependencyHelper;
		$this->directoryHelper = $directoryHelper;
		$this->rollbackHelper = $rollbackHelper;
		$this->migrationRepository = $migrationRepository;
	}

	/**
	 * @param \string $directory
	 * @param integer $priority
	 *
	 * @return $this
	 */
	public function addDirectory($directory, $priority = null)
	{
		$this->directories[] = new MigrationDirectory($directory, $priority);

		usort($this->directories, function (MigrationDirectory $a, MigrationDirectory $b) {
			if ($a->getPriority() == $b->getPriority()) {
				return 0;
			}

			return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
		});

		return $this;
	}

	/**
	 * @param bool $scopeDirectories
	 */
	public function setScopeDirectories(bool $scopeDirectories): void
	{
		$this->scopeDirectories = $scopeDirectories;
	}

	/**
	 * @return MigrationDirectory[]
	 */
	public function getMigrationDirectories()
	{
		return $this->directories;
	}

	/**
	 * @param SymfonyStyle $io
	 * @param string $directory
	 *
	 * @return $this
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function migrate(SymfonyStyle $io, $directory = null)
	{
		$this->setMigration(true);

		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);

		$io->title('Starting migration, directories:');

		/** @var ImportFile[] $files */
		$files = $this->fetchFiles($io, $directory, function (string $directory) use ($repo): \Closure {

			try {
				$latestMigration = $repo->getLatestMigration($this->directoryHelper->getCleanedPath($directory));
			} catch (\Exception $e) {
				$latestMigration = null;
			}

			return function (\SplFileInfo $file) use ($latestMigration) {
				if (
					$this->getFileIdentifier($file->getBasename())
					&& (!$latestMigration || $this->getFileIdentifier($file->getBasename()) > $latestMigration->getIdentifier())
				) {
					return true;
				}

				return false;
			};
		});

		if ($files) {
			$this->migrateFiles($io, $files);
		} else {
			$io->success('Everything is up to date.');
		}

		return $this;
	}

	/**
	 * @param bool $migration
	 *
	 * @return MigrationsManager
	 */
	public function setMigration(bool $migration): MigrationsManager
	{
		self::$migration = $migration;

		return $this;
	}

	/**
	 * @param SymfonyStyle $io
	 * @param null|string $directory
	 * @param \Closure $filterFn
	 *
	 * @return array
	 */
	private function fetchFiles(SymfonyStyle $io, ?string $directory, \Closure $filterFn): array
	{
		/** @var ImportFile[] $files */
		$files = [];

		$directories = $directory ? [$directory] : $this->getDirectories();
		$io->listing($directories);

		/** @var ImportFile[] $files */
		$tempFiles = [];
		foreach ($directories as $directory) {
			// Check if directory exists
			if (is_dir($directory)) {

				// Get Migration Files
				// Get All Done Identifiers
				// Reject Migrations Files
				// Execute Migrations Files & Write migration entries
				$finder = new Finder();
				$finder->files()->in($directory);
				$finder->filter($filterFn($directory));

				foreach ($finder as $file) {
					$tempFiles[] = new ImportFile($file, $this->em, $this->container);
				}
			} else {
				$io->error('Directory not found: ' . $directory);

				return [];
			}

			if ($this->scopeDirectories) {
				$files = array_merge($files, $this->sortAndFilterFiles($tempFiles));
			} else {
				$files = array_merge($files, $tempFiles);
			}

			$tempFiles = [];
		}

		if (!$this->scopeDirectories) {
			$files = array_unique($files);

			usort($files, function (ImportFile $x, ImportFile $y) {
				return strcmp($x->getFileIdentifier(), $y->getFileIdentifier());
			});
		}

		return $files;
	}

	/**
	 * @return \string[]
	 * @deprecated use getMigrationDirectories()
	 *
	 */
	public function getDirectories()
	{
		return array_map(function (MigrationDirectory $migrationDirectory) {
			return $migrationDirectory->getDirectory();
		}, $this->directories);
	}

	/**
	 * @param array|null $files
	 *
	 * @return array|null
	 */
	private function sortAndFilterFiles(?array $files): ?array
	{
		$files = array_unique($files);

		usort($files, function (ImportFile $x, ImportFile $y) {
			return strcmp($x->getFileIdentifier(), $y->getFileIdentifier());
		});

		return $files;
	}

	/**
	 * @param $basename
	 *
	 * @return string
	 */
	private function getFileIdentifier($basename)
	{
		preg_match('/V_(\d*)_.*/', $basename, $hits);

		return !empty($hits) ? $hits[1] : false;
	}

	/**
	 * @param SymfonyStyle $io
	 * @param array $files
	 *
	 * @throws \Exception
	 *
	 * @throws \Throwable
	 */
	private function migrateFiles(SymfonyStyle $io, array $files): void
	{
		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);
		$notes = [];

		// Execute migrations Files
		$io->progressStart(count($files));

		// Get new Sequence
		$sequence = $repo->getLatestSequence();
		$sequence++;

		$files = $this->dependencyHelper->resolveDependencies($files);

		/** @var ImportFile $file */
		foreach ($files as $file) {
			$io->writeln("\r<info> - Importing file: " . $file->getFile()->getBasename() . "</info>");
			$io->progressAdvance(1);

			try {
				// Start migration
				$file->migrate();
			} catch (\Exception $e) {
				$this->handleError($e, $io);
			}

			// Generate DB Entry
			$migration = new Migration();
			$migration
				->setDirectory($this->directoryHelper->getCleanedPath($file->getFile()->getPath()))
				->setIdentifier($file->getFileIdentifier())
				->setCreatedAt(new \DateTime())
				->setSequence($sequence);

			if ($note = $file->getNote()) {
				$notes[$file->getInstance()->getClassName()] = $note;
			}

			$this->em->persist($migration);
			$this->em->flush();
		}

		$io->progressFinish();
		$io->success('Finished, ' . count($files) . " files imported.");

		// Show Notes
		if (!empty($notes)) {
			$io->section("Migration Notes");
			$noteNumber = 1;

			foreach ($notes as $identifier => $note) {
				$io->section($noteNumber++ . ": " . $identifier);
				$io->note($note);
			}
		}

	}

	/**
	 * @param \Exception $exception
	 * @param SymfonyStyle $io
	 *
	 * @throws \Exception
	 */
	private function handleError(\Exception $exception, SymfonyStyle $io)
	{
		if (php_sapi_name() !== 'cli') {
			throw new \Exception($exception);
		}

		$io->error('Oops, there is an error :(');

		$errorChoices = [
			0 => 'Display Error',
			1 => 'Ignore Error',
			2 => 'Exit',
		];

		do {
			$io->newLine(1);
			$selectedChoice = $io->choice(' Whats to do next?', $errorChoices);

			switch ($selectedChoice) {
				case 'Display Error':
					$io->newLine(1);
					$io->error($exception->getMessage());
					break;

				case 'Exit':
					$io->warning('Exit migration');
					exit;
			}

		} while ($selectedChoice != 'Ignore Error');
	}

	/**
	 * @param SymfonyStyle $io
	 * @param string $directory
	 *
	 * @return $this
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	public function migrateFull(SymfonyStyle $io, $directory = null)
	{
		$this->setMigration(true);

		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);

		$io->warning('!!! FULL-MIGRATION !!!');
		$io->title('Starting migrations, directories:');

		/** @var ImportFile[] $files */
		$files = $this->fetchFiles($io, $directory, function (string $directory) use ($repo): \Closure {

			$doneMigrationIdentifiers = [];
			foreach ($repo->getAllMigrationIdentifiers($this->directoryHelper->getCleanedPath($directory)) as $migration) {
				$doneMigrationIdentifiers[] = $migration['identifier'];
			}

			return function (\SplFileInfo $file) use ($doneMigrationIdentifiers) {
				if (
					$this->getFileIdentifier($file->getBasename())
					&& !in_array($this->getFileIdentifier($file->getBasename()), $doneMigrationIdentifiers)
				) {
					return true;
				}

				return false;
			};
		});

		if ($files) {
			$this->migrateFiles($io, $files);
		} else {
			$io->success('Everything is up to date.');
		}

		return $this;
	}

	/**
	 * @param SymfonyStyle $io
	 *
	 * @return $this
	 *
	 * @throws \Exception
	 */
	public function rollback(SymfonyStyle $io)
	{
		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);

		$sequence = $repo->getLatestSequence();
		$io->title('Rollback from sequence ' . $sequence . ' to ' . ($sequence - 1));

		$sure = $io->ask('Are you sure you want to rollback?', true);

		if (!$sure) {
			$io->warning('Rollback skipped');

			return $this;
		}

		// Get files for Sequence
		/** @var ImportFile[] $files */
		$files = $this->rollbackHelper->getSequenceImportFiles($sequence, $this->directories);

		foreach ($files as $rollbackImportFile) {
			$rollbackImportFile->rollback();
			$io->writeln("\r<info> - Rolback file: " . $rollbackImportFile->getFile()->getBasename() . "</info>");
		}

		// Delete Migrations entries
		$this->migrationRepository->deleteFromSequence($sequence);

		$io->success('Successful rollback from ' . $sequence . ' to ' . ($sequence - 1));

		return $this;
	}
}