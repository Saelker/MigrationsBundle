<?php

namespace Saelker\MigrationsBundle;

use Saelker\MigrationsBundle\DependencyInjection\Compiler\MigrationsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SaelkerMigrationsBundle extends Bundle
{
	public function build(ContainerBuilder $container): void
	{
		parent::build($container);

		$container->addCompilerPass(new MigrationsCompilerPass(__DIR__ . "/Migrations"));
	}
}
