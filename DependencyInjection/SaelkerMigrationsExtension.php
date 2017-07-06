<?php

namespace Saelker\MigrationsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SaelkerMigrationsExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		if (array_key_exists('directories', $config)) {
			$this->addDirectories($config['directories'], $container);
		}

		if (array_key_exists('clean_depth', $config)) {
			$directoryHelper = $container->getDefinition('saelker.directory_helper');
			$directoryHelper->addArgument($config['clean_depth']);
		}
	}

	/**
	 * @param $directories
	 * @param ContainerBuilder $container
	 */
	private function addDirectories($directories, ContainerBuilder $container)
	{
		$manager = $container->getDefinition('saelker.migrations_manager');

		foreach ($directories as $directory) {
			$manager->addMethodCall('addDirectory', [$directory]);
		}
	}
}
