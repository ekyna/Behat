<?php

namespace Ekyna\Behat\BootstrapExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class BootstrapFileLoader
 * @package Ekyna\Behat\BootstrapExtension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BootstrapFileLoader
{
    /**
     * Define expected configuration structure and validate it's values.
     *
     * @param ArrayNodeDefinition $definition
     */
    public function configure(ArrayNodeDefinition $definition)
    {
        $definition
            ->children()
                ->booleanNode('require_once')
                    ->info('Whether or not the file should be loaded through "require_once".')
                    ->defaultFalse()
                ->end()
                ->scalarNode('bootstrap_path')
                    ->info('The path to your bootstrap file.')
                    ->isRequired()
                    ->validate()
                        ->ifTrue(function ($fileName) {
                            return !file_exists($fileName);
                        })->thenInvalid("Failed to find the bootstrap file.")
                ->end()
            ->end();
    }

    /**
     * Include configured bootstrap file.
     *
     * @param array $config
     */
    public function load(array $config)
    {
        $bootstrapFileName = realpath($config['bootstrap_path']);

        if ($config['require_once']) {
            /** @noinspection PhpIncludeInspection */
            require_once $bootstrapFileName;
            return;
        }
        /** @noinspection PhpIncludeInspection */
        require $bootstrapFileName;
    }
}
