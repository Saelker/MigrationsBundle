<?php

namespace Saelker\MigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('saelker_migrations');

		$rootNode
			->children()
			->arrayNode('directories')
			->prototype('scalar')->end()
			->end()
			->scalarNode('clean_depth')->defaultNull()->end()
			->scalarNode('directory_separator')->defaultValue('/')->end()
			->booleanNode('use_camel_case')->defaultFalse()->end()
			->booleanNode('ignore_errors')->defaultFalse()->end()
			->end();

		return $treeBuilder;
	}
}
