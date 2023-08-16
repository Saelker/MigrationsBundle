<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsRollbackCommand extends Command
{
	protected static $defaultName = 'saelker:migrations:rollback';
	protected static $defaultDescription = 'Rollback to last version';

	public function __construct(private readonly MigrationsManager $migrationsManager)
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$this->migrationsManager->rollback($io);

		return Command::SUCCESS;
	}
}