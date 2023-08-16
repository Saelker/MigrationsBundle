<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsMigrateCommand extends Command
{
	protected static $defaultDescription = 'Starts migrations';

	public function __construct(private readonly MigrationsManager $migrationsManager,
								private readonly DirectoryHelper $directoryHelper)
	{
		parent::__construct();
	}


	protected function configure(): void
	{
		parent::configure();

		$this
			->setName('saelker:migrations:migrate')
			->addOption(
				'select-directory',
				null,
				InputOption::VALUE_OPTIONAL,
				'If false or null all directories are used',
				false)
			->addOption(
				'install-directory',
				null,
				InputOption::VALUE_OPTIONAL,
				'will be used for initial installation',
				null);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$directory = false;
		if ($input->getOption('select-directory')) {
			$directory = $io->choice('Select a Directory', $this->directoryHelper->getSourceDirectories($this->migrationsManager->getMigrationDirectories()));
		}

		if ($input->getOption('install-directory')) {
			$directory = $input->getOption('install-directory');
		}

		$this->migrationsManager->migrate($io, $directory);

		return Command::SUCCESS;
	}
}