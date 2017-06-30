<?php

namespace Saelker\MigrationsBundle;

use Doctrine\ORM\EntityManager;

class MigrationsManager
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var string[]
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
	 * @param string $folder
	 * @return $this
	 */
	public function addFolder($folder)
	{
		$this->folders[] = $folder;

		return $this;
	}
}