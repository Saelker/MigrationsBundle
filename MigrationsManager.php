<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManagerInterface;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Helper\ConnectionHelper;
use Saelker\MigrationsBundle\Helper\DependencyHelper;
use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\Helper\RollbackHelper;
use Saelker\MigrationsBundle\Repository\MigrationRepository;
use Saelker\MigrationsBundle\Util\ImportFile;
use Saelker\MigrationsBundle\Util\MigrationDirectory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class MigrationsManager
{
	/**
	 * To check if symfony is loaded in migration mode
	 */
	public static $migration = false;
	/**
	 * @var MigrationDirectory[]
	 */
	private array $directories = [];
	private ContainerInterface $container;
	private ?bool $scopeDirectories = null;
	private readonly KernelInterface $kernel;

	public function __construct(private readonly EntityManagerInterface $em,
								private readonly DependencyHelper       $dependencyHelper,
								private readonly DirectoryHelper        $directoryHelper,
								private readonly RollbackHelper         $rollbackHelper,
								private readonly MigrationRepository    $migrationRepository,
								private readonly ConnectionHelper       $connectionHelper,
								KernelInterface                         $kernel)
	{
		$this->kernel = $kernel;
		$this->container = $kernel->getContainer();
	}

	public function addDirectory($directory, $priority = null): static
	{
		$this->directories[] = new MigrationDirectory($directory, $priority);

		usort($this->directories, static fn(MigrationDirectory $a, MigrationDirectory $b): int => $a->getPriority() <=> $b->getPriority());

		return $this;
	}

	public function setScopeDirectories(bool $scopeDirectories): void
	{
		$this->scopeDirectories = $scopeDirectories;
	}

	public function getMigrationDirectories(): array
	{
		return $this->directories;
	}

	public function migrate(SymfonyStyle $io, string $directory = null): static
	{
		$this->setMigration(true);

		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);

		$io->title('Starting migration, directories:');

		/** @var ImportFile[] $files */
		$files = $this->fetchFiles($io, $directory, function (string $directory) use ($repo): \Closure {

			try {
				$latestMigration = $repo->getLatestMigration($this->directoryHelper->getCleanedPath($directory));
			} catch (\Exception) {
				$latestMigration = null;
			}

			return function (\SplFileInfo $file) use ($latestMigration): bool {
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

	public function setMigration(bool $migration): MigrationsManager
	{
		self::$migration = $migration;

		return $this;
	}

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
					$tempFiles[] = new ImportFile($file, $this->kernel, $this->em, $this->container, $this->connectionHelper);
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

			usort($files, fn(ImportFile $x, ImportFile $y): int => strcmp((string)$x->getFileIdentifier(), (string)$y->getFileIdentifier()));
		}

		return $files;
	}

	public function getDirectories(): array
	{
		return array_map(static fn(MigrationDirectory $migrationDirectory) => $migrationDirectory->getDirectory(), $this->directories);
	}

	private function sortAndFilterFiles(?array $files): ?array
	{
		$files = array_unique($files);

		usort($files, fn(ImportFile $x, ImportFile $y): int => strcmp((string)$x->getFileIdentifier(), (string)$y->getFileIdentifier()));

		return $files;
	}

	private function getFileIdentifier($basename): bool|string
	{
		preg_match('/V_(\d*)_.*/', (string)$basename, $hits);

		return !empty($hits) ? $hits[1] : false;
	}

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

	private function handleError(\Exception $exception, SymfonyStyle $io): void
	{
		if (PHP_SAPI !== 'cli') {
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
		} while ($selectedChoice !== 'Ignore Error');
	}

	public function migrateFull(SymfonyStyle $io, string $directory = null): static
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

			return function (\SplFileInfo $file) use ($doneMigrationIdentifiers): bool {
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

	public function rollback(SymfonyStyle $io): static
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