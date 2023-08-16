<?php

namespace Saelker\MigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder('saelker_migrations');

		$treeBuilder
			->getRootNode()
			->children()
			->arrayNode('directories')
			->prototype('scalar')->end()
			->end()
			->scalarNode('clean_depth')->defaultNull()->end()
			->booleanNode('use_camel_case')->defaultFalse()->end()
			->booleanNode('ignore_errors')->defaultFalse()->end()
			->booleanNode('scope_directories')->defaultFalse()->end()
			->end();

		return $treeBuilder;
	}
}
