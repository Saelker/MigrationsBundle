<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsMigrateCommand extends ContainerAwareCommand
{
	/**
	 * @inheritdoc
	 */
	protected function configure()
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
				'')
			->setDescription('Starts migrations');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$migrationsManager = $this->getContainer()->get(MigrationsManager::class);
		$directoryHelper = $this->getContainer()->get(DirectoryHelper::class);

		$io = new SymfonyStyle($input, $output);

		$directory = false;
		if ($input->getOption('select-directory')) {
			$directory = $io->choice('Select a Directory', $directoryHelper->getSourceDirectories($migrationsManager->getDirectories()));
		}

		if ($input->getOption('install-directory')) {
			$directory = $input->getOption('install-directory');
		}

		$migrationsManager->migrate($io, $directory);
	}
}