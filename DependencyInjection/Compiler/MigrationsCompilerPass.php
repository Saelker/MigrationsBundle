<?php

namespace Saelker\MigrationsBundle\DependencyInjection\Compiler;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MigrationsCompilerPass implements CompilerPassInterface
{
	/**
	 * @var
	 */
	private $directory;

	/**
	 * @var integer
	 */
	private $priority;

	/**
	 * MigrationsCompilerPass constructor.
	 * @param $directory
	 * @param integer $priority
	 */
	public function __construct($directory, $priority = null)
	{
		$this->directory = $directory;
		$this->priority = $priority;
	}

	public function process(ContainerBuilder $container)
	{
		$container->getDefinition(MigrationsManager::class)->addMethodCall('addDirectory', [$this->directory, $this->priority]);
	}
}