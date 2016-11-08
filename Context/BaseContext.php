<?php

namespace Ekyna\Behat\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Context
 * @package Ekyna\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseContext extends MinkContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * Opens specified route with parameters
     *
     * @Given /^(?:|I )am on "(?P<route>[^"]+)" route with "(?P<parameters>[^"]+)"$/
     * @When  /^(?:|I )go to "(?P<route>[^"]+)" route with "(?P<parameters>[^"]+)"$/
     *
     * @param string $route
     * @param string $parameters
     */
    public function visitRouteWithParameters($route, $parameters)
    {
        $parametersArray = [];

        if (preg_match_all('~[a-zA-Z0-9]+\:[\d]+~', $parameters, $matches)) {
            foreach ($matches[0] as $match) {
                list($key, $value) = explode(':', $match);
                $parametersArray[$key] = $value;
            }
        }

        $this->visitPath($this->generatePath($route, $parametersArray));
    }

    /**
     * Opens specified route
     *
     * @Given I am on :route route
     * @When  I go to :route route
     *
     * @param string $route
     */
    public function visitRoute($route)
    {
        $this->visitPath($this->generatePath($route));
    }

    /**
     * Generates the path from the given route and parameters.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    private function generatePath($route, $parameters = [])
    {
        return $this
            ->getContainer()
            ->get('router')
            ->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
