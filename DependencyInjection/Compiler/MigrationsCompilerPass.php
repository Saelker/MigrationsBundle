<?php

namespace Saelker\MigrationsBundle\DependencyInjection\Compiler;

use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MigrationsCompilerPass implements CompilerPassInterface
{
	public function __construct(
		private $directory,
		private $priority = null
	)
	{
	}

	public function process(ContainerBuilder $container): void
	{
		$container->getDefinition(MigrationsManager::class)->addMethodCall('addDirectory', [$this->directory, $this->priority]);
	}
}