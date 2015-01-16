<?php

namespace Knp\JsonSchemaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
     protected $supportedDbDrivers = array('orm', 'mongodb');
    protected $supportedSources = array('reflection', 'serializer');

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('json_schema')
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('orm')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return strtolower($v); })
                    ->end()
                    ->validate()
                        ->ifNotInArray($this->supportedDbDrivers)
                        ->thenInvalid('The db driver %s is not supported. Please choose one of ' . implode(', ', $this->supportedDbDrivers))
                    ->end()
                ->end()
                ->scalarNode('source')
                    ->defaultValue('reflection')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return strtolower($v); })
                    ->end()
                    ->validate()
                        ->ifNotInArray($this->supportedSources)
                        ->thenInvalid('The source %s of information is not supported. Please choose one of ' . implode(', ', $this->supportedSources))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
