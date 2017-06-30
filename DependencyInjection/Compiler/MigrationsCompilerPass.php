<?php

namespace Saelker\MigrationsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MigrationsCompilerPass implements CompilerPassInterface
{
	/**
	 * @var
	 */
	private $folder;

	/**
	 * MigrationsCompilerPass constructor.
	 * @param $folder
	 */
	public function __construct($folder)
	{
		$this->folder = $folder;
	}

	public function process(ContainerBuilder $container)
	{
		$container->getDefinition('saelker.migrations_manager')->addMethodCall('addFolder', [$this->folder]);
	}
}