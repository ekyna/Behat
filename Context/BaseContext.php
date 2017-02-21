<?php

namespace Ekyna\Behat\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class BaseContext
 * @package Ekyna\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseContext extends MinkContext implements KernelAwareContext
{
    use KernelDictionary;

    /**
     * Opens specified route
     *
     * @Given /^(?:|I )am on "(?P<route>[^"]+)" route$/
     * @When  /^(?:|I )go to "(?P<route>[^"]+)" route$/
     *
     * @param string $route
     */
    public function visitRoute($route)
    {
        $this->visitPath($this->generatePath($route));
        $this->assertResponseStatus(200);
    }

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

        if (preg_match_all('~[a-zA-Z0-9]+\:[a-zA-Z0-9]+~', $parameters, $matches)) {
            foreach ($matches[0] as $match) {
                list($key, $value) = explode(':', $match);
                $parametersArray[$key] = $value;
            }
        }

        $this->visitPath($this->generatePath($route, $parametersArray));
        $this->assertResponseStatus(200);
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

    /**
     * Fills in hidden form field with specified name
     *
     * @When /^(?:|I )fill in hidden "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function iFillHiddenFieldWith($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);

        $this->getSession()->getPage()
            ->find('css', 'input[name="' . $field . '"]')->setValue($value);
    }
}
