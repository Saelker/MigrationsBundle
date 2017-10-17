<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\MigrationsManager;
use Saelker\MigrationsBundle\Util\MigrationDirectory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsInfoCommand extends ContainerAwareCommand
{
	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		parent::configure();

		$this
			->setName('saelker:migrations:info')
			->setDescription('Show infos from migrations manager');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var MigrationsManager $migrationsManager */
		$migrationsManager = $this->getContainer()->get(MigrationsManager::class);

		$io = new SymfonyStyle($input, $output);

		$io->title('Directories');

		$migrationDirectories = $migrationsManager->getMigrationDirectories();
		$directoryStrings = [];

		/** @var MigrationDirectory $migrationDirectory */
		foreach ($migrationDirectories as $migrationDirectory) {
			$directoryStrings[] = "Priority: {$migrationDirectory->getPriority()} - Path: {$migrationDirectory->getDirectory()}";
		}

		$io->listing($directoryStrings);
	}
}