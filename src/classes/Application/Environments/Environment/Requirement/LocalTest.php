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
 * If it is set as true, this requirement returns true
 *
 * @package Application
 * @subpackage Environments
 * @author Emre Celebi <emre.celebi@ionos.com>
 */
class Application_Environments_Environment_Requirement_LocalTest extends Application_Environments_Environment_Requirement
{
    /**
     * @var bool
     */
    protected $isLocalTest = false;

    public function __construct()
    {
        if (defined('APP_TESTS_RUNNING'))
        {
            $this->isLocalTest = APP_TESTS_RUNNING;
        }
    }

    public function isValid() : bool
    {
        return $this->isLocalTest === true;
    }
}