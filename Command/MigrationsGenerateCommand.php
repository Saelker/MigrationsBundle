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
	protected static $defaultName = 'saelker:migrations:generate';
	protected static $defaultDescription = 'Generate a new migrations file';

	public function __construct(private readonly MigrationsManager $migrationsManager,
								private readonly DirectoryHelper $directoryHelper,
								private readonly MigrationRepository $migrationRepository)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
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

		return Command::SUCCESS;
	}
}