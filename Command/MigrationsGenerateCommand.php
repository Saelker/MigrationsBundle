<?php

namespace Saelker\MigrationsBundle\Command;

use Saelker\MigrationsBundle\Entity\Migration;
use Saelker\MigrationsBundle\Util\GenerateMigration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationsGenerateCommand extends ContainerAwareCommand
{
	/**
	 * @inheritdoc
	 */
	protected function configure()
	{
		parent::configure();

		$this
			->setName('saelker:migrations:generate')
			->setDescription('Generate a new migrations file');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$migrationsManager = $this->getContainer()->get('saelker.migrations_manager');
		$repo = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getRepository(Migration::class);

		$io = new SymfonyStyle($input, $output);

		$io->title('Generate a new migrations file');

		$directory = $io->choice('Select a Directory', $migrationsManager->getDirectories());
		$namespace = $io->ask('Namespace', GenerateMigration::getNamespaceFromDirectory($directory));
		$description = $io->ask('Description');

		// Generate file
		$file = GenerateMigration::generate($namespace, $repo->getNextIdentifier($directory), $description, $directory);

		$io->success('Migration file was generated: '. $file);
	}
}