<?php

namespace Ekyna\Behat\BootstrapExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BootstrapExtension
 * @package Ekyna\Behat\BootstrapExtension\ServiceContainer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BootstrapExtension implements ExtensionInterface
{
    /**
     * @var BootstrapFileLoader
     */
    private $loader;


    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->loader = new BootstrapFileLoader();
    }

    /**
     * @inheritdoc
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loader->load($config);
    }

    /**
     * @inheritdoc
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $this->loader->configure($builder);
    }

    /**
     * @inheritdoc
     */
    public function getConfigKey()
    {
        return 'bootstrap';
    }

    /**
     * @inheritdoc
     */
    public function initialize(ExtensionManager $extensionManager)
    {

    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {

    }
}
