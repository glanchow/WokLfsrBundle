<?php
namespace Wok\LfsrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration Class
 */
class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wok_lfsr');

        $rootNode
            ->children()
            ->integerNode('feedback')
                ->min(1)
                ->defaultValue(0xC)
            ->end()
            ->scalarNode('state')
                ->defaultValue(1)
            ->end()
            ->scalarNode('base')
                ->defaultNull()
            ->end()
            ->booleanNode('pad')
                ->defaultFalse()
            ->end()
            ->end();

        return $treeBuilder;
    }

}
