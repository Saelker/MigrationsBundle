<?php

namespace Saelker\MigrationsBundle\DependencyInjection;

use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SaelkerMigrationsExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container): void
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		if (array_key_exists('directories', $config)) {
			$this->addDirectories($config['directories'], $container, $config['scope_directories']);
		}

		// Add configs to directory helper
		$directoryHelper = $container->getDefinition(DirectoryHelper::class);
		$directoryHelper->addArgument($config['clean_depth']);
		$directoryHelper->addArgument($config['use_camel_case']);
	}

	private function addDirectories($directories, ContainerBuilder $container, bool $scopeDirectories): void
	{
		$manager = $container->getDefinition(MigrationsManager::class);

		foreach ($directories as $directory) {
			$manager->addMethodCall('addDirectory', [$directory]);
		}

		$manager->addMethodCall('setScopeDirectories', [$scopeDirectories]);
	}
}
