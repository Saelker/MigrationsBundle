<?php

namespace Saelker\MigrationsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
			->setDescription('Starts migrations');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$migrationsManager = $this->getContainer()->get('saelker.migrations_manager');
		$io = new SymfonyStyle($input, $output);
		$migrationsManager->migrate($io);
	}
}