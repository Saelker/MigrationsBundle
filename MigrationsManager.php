<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManager;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\Repository\MigrationRepository;
use Saelker\MigrationsBundle\Util\ImportFile;
use Saelker\MigrationsBundle\Util\MigrationDirectory;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class MigrationsManager
{
	/**
	 * @var EntityManager
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
	 * MigrationsManager constructor.
	 * @param EntityManager $em
	 * @param ContainerInterface $container
	 */
	public function __construct(EntityManager $em, ContainerInterface $container)
	{
		$this->em = $em;
		$this->container = $container;
	}

	/**
	 * @param \string $directory
	 * @param integer $priority
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
	 * @deprecated use getMigrationDirectories()
	 * @return \string[]
	 */
	public function getDirectories()
	{
		return array_map(function (MigrationDirectory $migrationDirectory) {
			return $migrationDirectory->getDirectory();
		}, $this->directories);
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
	 * @return $this
	 * @throws \Exception
	 */
	public function migrate(SymfonyStyle $io, $directory = null)
	{
		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);
		$directoryHelper = $this->container->get(DirectoryHelper::class);

		$io->title('Starting migration, directories:');

		/** @var ImportFile[] $files */
		$files = [];

		$directories = $directory ? [$directory] : $this->getDirectories();
		$io->listing($directories);

		foreach ($directories as $directory) {
			// Check if directory exists
			if (is_dir($directory)) {

				// Get Migration Files
				// Get Last Identifier
				// Reject Migrations Files
				// Execute Migrations Files & Write migration entries
				try {
					$latestMigration = $repo->getLatestMigration($directoryHelper->getCleanedPath($directory));
				} catch (\Exception $e) {
					$latestMigration = null;
				}

				$finder = new Finder();
				$finder->files()->in($directory);
				$finder->filter(function (\SplFileInfo $file) use ($latestMigration) {
					if (
						$this->getFileIdentifier($file->getBasename())
						&& (!$latestMigration || $this->getFileIdentifier($file->getBasename()) > $latestMigration->getIdentifier())
					) {
						return true;
					}

					return false;
				});

				foreach ($finder as $file) {
					$files[] = new ImportFile($file, $this->em, $this->container);
				}
			} else {
				$io->error('Directory not found: ' . $directory);
				return $this;
			}
		}

		$files = array_unique($files);

		usort($files, function (ImportFile $x, ImportFile $y) {
			return strcmp($x->getFileIdentifier(), $y->getFileIdentifier());
		});

		if ($files) {
			// Execute migrations Files
			$io->progressStart(count($files));

			// Get new Sequence
			$sequence = $repo->getLatestSequence();
			$sequence++;

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
					->setDirectory($directoryHelper->getCleanedPath($file->getFile()->getPath()))
					->setIdentifier($file->getFileIdentifier())
					->setCreatedAt(new \DateTime())
					->setSequence($sequence);

				$this->em->persist($migration);
				$this->em->flush();

			}

			$io->progressFinish();

			$io->success('Finished, ' . count($files) . " files imported.");


		} else {
			$io->success('Everything is up to date.');
		}

		return $this;
	}

	/**
	 * @param SymfonyStyle $io
	 * @param string $directory
	 * @return $this
	 * @throws \Exception
	 */
	public function migrateDev(SymfonyStyle $io, $directory = null)
	{
		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);
		$directoryHelper = $this->container->get(DirectoryHelper::class);

		$io->note('!!! DEVELOPMENT !!!');
		$io->title('Starting migrations, directories:');

		/** @var ImportFile[] $files */
		$files = [];

		$directories = $directory ? [$directory] : $this->getDirectories();
		$io->listing($directories);

		foreach ($directories as $directory) {
			// Check if directory exists
			if (is_dir($directory)) {

				// Get Migration Files
				// Get All Done Identifiers
				// Reject Migrations Files
				// Execute Migrations Files & Write migration entries
				$doneMigrationIdentifiers = [];
				foreach($repo->getAllMigrationIdentifiers($directoryHelper->getCleanedPath($directory)) as $migration) {
					$doneMigrationIdentifiers[] = $migration['identifier'];
				}

				$finder = new Finder();
				$finder->files()->in($directory);
				$finder->filter(function (\SplFileInfo $file) use ($doneMigrationIdentifiers) {
					if (
						$this->getFileIdentifier($file->getBasename())
						&& !in_array($this->getFileIdentifier($file->getBasename()), $doneMigrationIdentifiers)
					) {
						return true;
					}

					return false;
				});

				foreach ($finder as $file) {
					$files[] = new ImportFile($file, $this->em, $this->container);
				}
			} else {
				$io->error('Directory not found: ' . $directory);
				return $this;
			}
		}

		$files = array_unique($files);

		usort($files, function (ImportFile $x, ImportFile $y) {
			return strcmp($x->getFileIdentifier(), $y->getFileIdentifier());
		});

		if ($files) {
			// Execute migrations Files
			$io->progressStart(count($files));

			// Get new Sequence
			$sequence = $repo->getLatestSequence();
			$sequence++;

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
					->setDirectory($directoryHelper->getCleanedPath($file->getFile()->getPath()))
					->setIdentifier($file->getFileIdentifier())
					->setCreatedAt(new \DateTime())
					->setSequence($sequence);

				$this->em->persist($migration);
				$this->em->flush();

			}

			$io->progressFinish();

			$io->success('Finished, ' . count($files) . " files imported.");


		} else {
			$io->success('Everything is up to date.');
		}

		return $this;
	}

	/**
	 * @param SymfonyStyle $io
	 * @return $this
	 */
	public function rollback(SymfonyStyle $io)
	{
		/** @var MigrationRepository $repo */
		$repo = $this->em->getRepository(Migration::class);
		$directoryHelper = $this->container->get(DirectoryHelper::class);

		$sequence = $repo->getLatestSequence();
		$io->title('Rollback from sequence ' . $sequence . ' to ' . ($sequence - 1));

		/** @var ImportFile[] $files */
		$files = [];

		//TODO Rolback

		return $this;
	}

	/**
	 * @param $basename
	 * @return string
	 */
	private function getFileIdentifier($basename)
	{
		preg_match('/V_(\d*)_.*/', $basename, $hits);

		return !empty($hits) ? $hits[1] : false;
	}

	/**
	 * @param \Exception $exception
	 * @param SymfonyStyle $io
	 * @throws \Exception
	 */
	private function handleError(\Exception $exception, SymfonyStyle $io)
	{
		$io->error('Oops, there is an error :(');

		$errorChoices = [
			0 => 'Display Error',
			1 => 'Ignore Error',
			2 => 'Exit'
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
}