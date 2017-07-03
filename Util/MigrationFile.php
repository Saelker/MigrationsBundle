<?php

namespace Saelker\MigrationsBundle\Util;

use Doctrine\ORM\EntityManager;

abstract class MigrationFile
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * MigrationFile constructor.
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	abstract public function up();
}