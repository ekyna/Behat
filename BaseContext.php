<?php

namespace Ekyna\Behat;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class Context
 * @package Ekyna\Behat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseContext extends MinkContext implements KernelAwareContext
{
    use KernelDictionary;
}
