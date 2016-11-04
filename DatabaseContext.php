<?php

namespace Ekyna\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

/**
 * Class DatabaseContext
 * @package Ekyna\Behat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DatabaseContext extends Context
{
    /**
     * @param BeforeScenarioScope $scope
     * @BeforeScenario
     */
    public function initialiseDatabase(BeforeScenarioScope $scope)
    {
        $conn = $this->getContainer()->get('doctrine.dbal.default_connection');

        $stop = true;
    }
}
