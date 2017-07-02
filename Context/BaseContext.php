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
     * @var int
     */
    private $defaultWaitTimeout = 7000;


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
     * Clicks link with specified id|title|alt|text
     *
     * @When /^(?:|I )click "(?P<link>(?:[^"]|\\")*)"$/
     *
     * @param string $link
     */
    public function clickLink($link)
    {
        parent::clickLink($link);
    }

    /**
     * Show the given tab.
     *
     * @When /^(?:|I )show the "(?P<tab>(?:[^"]|\\")*)" tab$/
     *
     * @param string $tab
     */
    public function showTab($tab)
    {
        $this->clickLink('toggle-' . $tab);
        $this->waitXSeconds(1);
    }

    /**
     * Wait X seconds.
     *
     * @When /^(?:|I )wait "(?P<seconds>(?:[^"]|\\")*)" seconds/
     *
     * @param int $seconds
     */
    public function waitXSeconds($seconds)
    {
        $this->getJavascriptDriver()->wait($seconds*1000, '!true');
    }

    /**
     * Wait for modal to appear.
     *
     * @When /^(?:|I )wait for the modal to appear/
     */
    public function waitForModalShown()
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && (1 == jQuery('.modal:visible').size()) && (1 == jQuery('.modal').css('opacity')) 
EOT
        );
    }

    /**
     * Wait for form to appear.
     *
     * @When /^(?:|I )wait for the form "(?P<form>(?:[^"]|\\")*)" to appear/
     *
     * @param string $form
     */
    public function waitForFormShown($form)
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && (1 == jQuery('form[name="$form"]:visible').size()) 
EOT
        );
    }

    /**
     * Wait for modal to disappear.
     *
     * @When /^(?:|I )wait for the modal to disappear/
     */
    public function waitForModalHidden()
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && (0 == jQuery('.modal:visible').size()) 
EOT
        );
    }

    /**
     * Wait for Select2 initialization on field.
     *
     * @When /^(?:|I )wait for Select2 initialization on "(?P<field>(?:[^"]|\\")*)"/
     *
     * @param string $field
     */
    public function waitSelect2InitializationOnField($field)
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && (undefined != jQuery.fn.select2) && jQuery('[name="$field"]:visible').hasClass('select2-hidden-accessible')
EOT
        );
    }

    /**
     * Wait for field to be enabled.
     *
     * @When /^(?:|I )wait for "(?P<field>(?:[^"]|\\")*)" to be enabled/
     *
     * @param string $field
     */
    public function waitForFieldEnabled($field)
    {
        $this->getJavascriptDriver()->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && !jQuery('[name="$field"]:visible').is(':disabled')
EOT
        );
    }

    /**
     * Search in a (Select2) field and select.
     *
     * @When /^(?:|I )search "(?P<value>(?:[^"]|\\")*)" in "(?P<field>(?:[^"]|\\")*)" and select the first result$/
     *
     * @param string $value
     * @param string $field
     */
    public function searchAndSelectFirstResult($value, $field)
    {
        $driver = $this->getJavascriptDriver();

        $this->waitSelect2InitializationOnField($field);

        $driver->evaluateScript("$('select[name=\"$field\"]').select2('open');");
        $driver->evaluateScript("$('.select2-container--open .select2-search__field').val('$value').keyup();");
        $driver->wait($this->defaultWaitTimeout, '0 < jQuery("li.select2-results__option--highlighted").size()');
        $driver->evaluateScript("$('li.select2-results__option--highlighted').mouseup();");
    }

    /**
     * Fills in form tinymce field with specified id|name|label|value
     *
     * @When /^(?:|I )fill in tinymce "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in tinymce "(?P<field>(?:[^"]|\\")*)" with:$/
     * @When /^(?:|I )fill in tinymce "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @param string $field
     * @param string $value
     */
    public function fillTinymce($field, $value)
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            parent::fillField($field, $value);

            return;
        }

        $id = $this->getFieldIdFromName($field);

        $driver->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('tinymce') && null != tinymce.get('$id')     
EOT
        );
        $driver->evaluateScript(<<<EOT
            tinymce.get('$id').setContent('<p>$value</p>')
EOT
        );
    }

    /**
     * Add element to collection field with specified id
     *
     * @When /^(?:|I )add element to collection field "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @param string $field
     */
    public function addElementToCollection($field)
    {
        $driver = $this->getJavascriptDriver();

        $driver->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && jQuery('#{$field}').attr('data-initialized') == 1     
EOT
        );
        $driver->evaluateScript(<<<EOT
            $('[data-collection="{$field}"][data-collection-role="add"]').click();
EOT
        );
    }

    /**
     * Remove element with specified index form collection field with specified id
     *
     * @When /^(?:|I )remove element with index "(?P<index>(?:[^"]|\\")*)" from collection field "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @param int $index
     * @param string $field
     */
    public function removeElementToCollection($index, $field)
    {
        $driver = $this->getJavascriptDriver();

        $driver->wait($this->defaultWaitTimeout, <<<EOT
            window.hasOwnProperty('jQuery') && jQuery('#{$field}').attr('data-initialized') == 1     
EOT
        );
        $driver->evaluateScript(<<<EOT
            $('[data-field="{$field}_{$index}"][data-collection-role="remove"]').removeAttr('data-confirm').click();
EOT
        );
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

    /**
     * Returns the field id from the field name.
     *
     * @param string $name
     *
     * @return string
     */
    private function getFieldIdFromName($name)
    {
        if (false === preg_match_all('~\[?[a-zA-Z0-9_]+\]?~', $name, $matches)) {
            throw new \InvalidArgumentException("Unexpected field name '{$name}'.");
        }

        return implode('_', array_map(function($val) {
            return trim($val, '[]');
        }, $matches[0]));
    }
}
