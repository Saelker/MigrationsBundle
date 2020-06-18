<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsInfoCommand extends Command
{
	/**
	 * @var MigrationsManager
	 */
	private $migrationsManager;

	/**
	 * Constructor.
	 *
	 * @param MigrationsManager $migrationsManager
	 */
	public function __construct(MigrationsManager $migrationsManager)
	{
		parent::__construct();

		$this->migrationsManager = $migrationsManager;
	}

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
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$io->title('Directories');

		$migrationDirectories = $this->migrationsManager->getMigrationDirectories();
		$directoryStrings = [];

		foreach ($migrationDirectories as $migrationDirectory) {
			$directoryStrings[] = "Priority: {$migrationDirectory->getPriority()} - Path: {$migrationDirectory->getDirectory()}";
		}

		$io->listing($directoryStrings);
	}
}