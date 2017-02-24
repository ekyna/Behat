<?php

namespace Ekyna\Behat\Context;

use Behat\Mink\Driver\Selenium2Driver;
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
        //$this->assertResponseStatus(200); TODO Does not work with Selenium2Driver
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
        // $this->assertResponseStatus(200); TODO Does not work with Selenium2Driver
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
            ->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Fills in hidden form field with specified name
     *
     * @param string $field
     * @param string $value
     *
     * @When /^(?:|I )fill in hidden "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function fillHiddenFieldWith($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);

        $this->getSession()->getPage()
            ->find('css', 'input[name="' . $field . '"]')->setValue($value);
    }

    /**
     * Show the given tab.
     *
     * @param string $tab The tab id
     *
     * @When /^(?:|I )show the "(?P<tab>(?:[^"]|\\")*)" tab$/
     */
    public function showTab($tab)
    {
        $this->clickLink('toggle-' . $tab);
    }

    /**
     * Wait X seconds.
     *
     * @param int $seconds
     *
     * @When /^(?:|I )wait "(?P<seconds>(?:[^"]|\\")*)" seconds/
     */
    public function waitXSeconds($seconds)
    {
        $this->getJavascriptDriver()->wait($seconds*1000, '!true');
    }

    /**
     * Wait for Select2 initialization on field.
     *
     * @param string $field
     *
     * @When /^(?:|I )I wait for Select2 initialization on "(?P<field>(?:[^"]|\\")*)"/
     */
    public function waitSelect2InitializationOnField($field)
    {
        $this->getJavascriptDriver()->wait(5000,
            'window.hasOwnProperty("jQuery") && jQuery("[name=\"'.$field.'\"]").hasClass("select2-hidden-accessible")'
        );
    }

    /**
     * Search in a (Select2) field and select.
     *
     * @param string $value
     * @param string $field
     *
     * @When /^(?:|I )search "(?P<value>(?:[^"]|\\")*)" in "(?P<field>(?:[^"]|\\")*)" and select the first result$/
     */
    public function searchAndSelectFirstResult($value, $field)
    {
        $driver = $this->getJavascriptDriver();

        $this->waitSelect2InitializationOnField($field);
//        $driver->wait(1000, 'false');

        $driver->evaluateScript("$('select[name=\"$field\"]').select2('open');");
        $driver->evaluateScript("$('.select2-search__field').val('". $value ."').keyup();");
        $driver->wait(5000, '0 < jQuery(".select2-results__options li span").size()');
        $driver->evaluateScript("$('.select2-results__options li:first-child').click();");
    }

    /**
     * Asserts that the driver supports javascript and returns it.
     *
     * @return \Behat\Mink\Driver\DriverInterface
     * @throws \Exception
     */
    private function getJavascriptDriver()
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof Selenium2Driver) {
            throw new \Exception('Unsupported driver');
        }

        return $driver;
    }
}
