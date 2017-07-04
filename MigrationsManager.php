<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManager;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\ImportFile;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class MigrationsManager
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var \string[]
	 */
	private $directories;

	/**
	 * MigrationsManager constructor.
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param \string $directory
	 * @return $this
	 */
	public function addDirectory($directory)
	{
		$this->directories[] = $directory;

		return $this;
	}

	/**
	 * @return \string[]
	 */
	public function getDirectories()
	{
		return $this->directories;
	}

	/**
	 * @param SymfonyStyle $io
	 * @return $this
	 */
	public function migrate(SymfonyStyle $io)
	{
		$repo = $this->em->getRepository(Migration::class);

		$io->title('Starting migration, directories:');
		$io->listing($this->getDirectories());

		/** @var ImportFile[] $files */
		$files = [];

		foreach ($this->getDirectories() as $directory) {
			// Check if directory exists
			if (is_dir($directory)) {

				// Get Migration Files
				// Get Last Identifier
				// Reject Migrations Files
				// Execute Migrations Files & Write migration entries
				$latestMigration = $repo->getLatestMigration($directory);

				$finder = new Finder();
				$finder->files()->in($directory);
				$finder->filter(function (\SplFileInfo $file) use ($latestMigration) {
					if ($this->getFileIdentifier($file->getBasename()) && ($this->getFileIdentifier($file->getBasename()) > $latestMigration->getIdentifier() || !$latestMigration)) {
						return true;
					}

					return false;
				});

				foreach($finder as $file) {
					$files[] = new ImportFile($file, $this->em);
				}
			} else {
				$io->error('Directory not found: ' . $directory);
				return $this;
			}
		}

		if ($files) {
			// Execute migrations Files
			$io->progressStart(count($files));

			/** @var ImportFile $file */
			foreach($files as $file) {
				$io->writeln("\r<info> - Importing file: " . $file->getFile()->getBasename()."</info>");
				$io->progressAdvance(1);

				// Start migration
				$file->migrate();

				// Generate DB Entry
				$migration = new Migration();
				$migration
					->setDirectory($file->getFile()->getPath())
					->setIdentifier($file->getFileIdentifier())
					->setCreatedAt(new \DateTime());

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
	 * @param $basename
	 * @return string
	 */
	private function getFileIdentifier($basename)
	{
		preg_match('/^.*_(\d*)/', $basename, $hits);

		return !empty($hits) ? $hits[1] : false;
	}
}