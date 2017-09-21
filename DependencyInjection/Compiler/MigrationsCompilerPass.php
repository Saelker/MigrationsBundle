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
	 * MigrationsCompilerPass constructor.
	 * @param $directory
	 */
	public function __construct($directory)
	{
		$this->directory = $directory;
	}

	public function process(ContainerBuilder $container)
	{
		$container->getDefinition(MigrationsManager::class)->addMethodCall('addDirectory', [$this->directory]);
	}
}