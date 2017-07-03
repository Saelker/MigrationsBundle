<?php

namespace Saelker\MigrationsBundle\DependencyInjection\Compiler;

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
		$container->getDefinition('saelker.migrations_manager')->addMethodCall('addDirectory', [$this->directory]);
	}
}