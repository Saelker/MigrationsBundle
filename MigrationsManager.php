<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManager;
use Saelker\MigrationsBundle\Entity\Migration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class MigrationsManager
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var \string[]
	 */
	private $folders;

	/**
	 * MigrationsManager constructor.
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @param \string $folder
	 * @return $this
	 */
	public function addFolder($folder)
	{
		$this->folders[] = $folder;

		return $this;
	}

	/**
	 * @return \string[]
	 */
	public function getFolders()
	{
		return $this->folders;
	}

	/**
	 * @return $this
	 */
	public function migrate()
	{
		$repo = $this->em->getRepository(Migration::class);

		/** @var \string $folder */
		foreach($this->getFolders() as $folder) {
			// Check if folder exists
			if (is_dir($folder)) {
				// Get Migration Files
				// Get Last Identifier
				// Reject Migrations Files
				// Execute Migrations Files & Write migration entries
				$lastIdentifier = $repo->getLatestIdentifier($folder);

				$finder = new Finder();
				$finder->in($folder);
				$finder->filter(function(\SplFileInfo $file) use ($lastIdentifier) {
					if ($file->getBasename('.php') < $lastIdentifier) {
						return false;
					}
				});
			}
		}

		return $this;
	}
}