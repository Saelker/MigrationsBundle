<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsInfoCommand extends Command
{
	protected static $defaultName = 'saelker:migrations:info';
	protected static $defaultDescription = 'Show infos from migrations manager';

	public function __construct(private readonly MigrationsManager $migrationsManager)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$io->title('Directories');

		$migrationDirectories = $this->migrationsManager->getMigrationDirectories();
		$directoryStrings = [];

		foreach ($migrationDirectories as $migrationDirectory) {
			$directoryStrings[] = "Priority: {$migrationDirectory->getPriority()} - Path: {$migrationDirectory->getDirectory()}";
		}

		$io->listing($directoryStrings);

		return Command::SUCCESS;
	}
}