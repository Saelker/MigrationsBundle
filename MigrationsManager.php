<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManager;
use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\ImportFile;
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
	 * @var \string[]
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
		$directoryHelper = $this->container->get('saelker.directory_helper');

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
				try {
					$latestMigration = $repo->getLatestMigration($directoryHelper->getCleanedPath($directory));
				} catch (\Exception $e) {
					$latestMigration = null;
				}

				$finder = new Finder();
				$finder->files()->in($directory);
				$finder->filter(function (\SplFileInfo $file) use ($latestMigration) {
					if ($this->getFileIdentifier($file->getBasename()) && (!$latestMigration || $this->getFileIdentifier($file->getBasename()) > $latestMigration->getIdentifier())) {
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

			// Get new Sequence
            try {
                $sequence = $this
                    ->em
                    ->getRepository(Migration::class)
                    ->createQueryBuilder('m')
                    ->select('MAX(m.sequence)')
                    ->getQuery()
                    ->getOneOrNullResult();

                $sequence++;
            } catch (\Exception $e) {
                $sequence = 0;
            }

			/** @var ImportFile $file */
			foreach($files as $file) {
				$io->writeln("\r<info> - Importing file: " . $file->getFile()->getBasename()."</info>");
				$io->progressAdvance(1);

				// Start migration
				$file->migrate();

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
	 * @param $basename
	 * @return string
	 */
	private function getFileIdentifier($basename)
	{
		preg_match('/V_(\d*)_.*/', $basename, $hits);

		return !empty($hits) ? $hits[1] : false;
	}
}