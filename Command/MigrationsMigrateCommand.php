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
	/**
	 * @var MigrationsManager
	 */
	private $migrationsManager;

	/**
	 * @var DirectoryHelper
	 */
	private $directoryHelper;

	/**
	 * Constructor.
	 *
	 * @param MigrationsManager $migrationsManager
	 * @param DirectoryHelper $directoryHelper
	 */
	public function __construct(MigrationsManager $migrationsManager,
								DirectoryHelper $directoryHelper)
	{
		parent::__construct();

		$this->migrationsManager = $migrationsManager;
		$this->directoryHelper = $directoryHelper;
	}


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
				null)
			->setDescription('Starts migrations');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 *
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
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
	}
}