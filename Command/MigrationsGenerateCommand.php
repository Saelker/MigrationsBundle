<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\MigrationsManager;
use Saelker\MigrationsBundle\Repository\MigrationRepository;
use Saelker\MigrationsBundle\Util\GenerateMigration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsGenerateCommand extends Command
{
	/**
	 * @var MigrationsManager
	 */
	private $migrationsManager;

	/**
	 * @var DirectoryHelper
	 */
	private $directoryHelper;

	/**
	 * @var MigrationRepository
	 */
	private $migrationRepository;

	/**
	 * Constructor.
	 *
	 * @param MigrationsManager $migrationsManager
	 * @param DirectoryHelper $directoryHelper
	 * @param MigrationRepository $migrationRepository
	 */
	public function __construct(MigrationsManager $migrationsManager,
								DirectoryHelper $directoryHelper,
								MigrationRepository $migrationRepository)
	{
		parent::__construct();

		$this->migrationsManager = $migrationsManager;
		$this->directoryHelper = $directoryHelper;
		$this->migrationRepository = $migrationRepository;
	}

	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		parent::configure();

		$this
			->setName('saelker:migrations:generate')
			->setDescription('Generate a new migrations file');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$io->title('Generate a new migrations file');

		$directory = $io->choice('Select a Directory', $this->directoryHelper->getSourceDirectories($this->migrationsManager->getMigrationDirectories()));
		$namespace = $io->ask('Namespace', GenerateMigration::getNamespaceFromDirectory($directory));
		$description = $io->ask('Description');
		$note = $io->ask('Note', false);

		// Generate file
		$file = GenerateMigration::generate($namespace, $this->migrationRepository->getNextIdentifier($directory), $description, $directory, $note);

		$io->success('Migration file was generated: ' . $file);
	}
}