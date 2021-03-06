<?php

namespace Saelker\MigrationsBundle\DependencyInjection;

use Saelker\MigrationsBundle\Helper\DirectoryHelper;
use Saelker\MigrationsBundle\MigrationsManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SaelkerMigrationsExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		if (array_key_exists('directories', $config)) {
			$this->addDirectories($config['directories'], $container, $config['scope_directories']);
		}

		// Add configs to directory helper
		$directoryHelper = $container->getDefinition(DirectoryHelper::class);
		$directoryHelper->addArgument($config['clean_depth']);
		$directoryHelper->addArgument($config['use_camel_case']);
	}

	/**
	 * @param $directories
	 * @param ContainerBuilder $container
	 * @param bool $scopeDirectories
	 */
	private function addDirectories($directories, ContainerBuilder $container, bool $scopeDirectories)
	{
		$manager = $container->getDefinition(MigrationsManager::class);

		foreach ($directories as $directory) {
			$manager->addMethodCall('addDirectory', [$directory]);
		}

		$manager->addMethodCall('setScopeDirectories', [$scopeDirectories]);
	}
}
