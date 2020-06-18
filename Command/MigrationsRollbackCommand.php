<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsRollbackCommand extends Command
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
			->setName('saelker:migrations:rollback')
			->setDescription('Rollback to last version');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 *
	 * @throws \Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$this->migrationsManager->rollback($io);
	}
}