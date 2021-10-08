<?php
/**
 * File containing the {@link Application_Environments_Environment_Requirement_LocalTest} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment_Requirement_LocalTest
 */

declare(strict_types=1);

/**
 * Check defined APP_TEST_RUNNING constant in config files
 * If it is set as true, this requirement returns true.
 *
 * @package Application
 * @subpackage Environments
 * @author Emre Celebi <emre.celebi@ionos.com>
 *
 * @see Application::isUnitTestingRunning()
 */
class Application_Environments_Environment_Requirement_LocalTest extends Application_Environments_Environment_Requirement
{
    public function isValid() : bool
    {
        return Application::isUnitTestingRunning();
    }
}
