<?php
/**
 * File containing the {@link Application_HealthMonitor_Component_Database} class.
 * @package Application
 * @subpackage HealthMonitor
 */

/**
 * The base component class.
 * @see Application_HealthMonitor_Component
 */
require_once 'Application/HealthMonitor/Component.php';

/**
 * Checks the database connectivity and speed.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_HealthMonitor_Component_AdminAPI extends Application_HealthMonitor_Component
{
    public function getName()
    {
        return 'UI layer';
    }

    public function getDescription()
    {
        return 'Integrity of the UI layer, accessibility of all screens.';
    }

    public function getYellowPagesURL()
    {
        return '';
    }

    public function getSeverity()
    {
        return self::SEVERITY_BLOCKER;
    }

    public function collectData()
    {
        $this->durationStart();
        
        $api = Application::createAPI();
        $method = $api->loadMethod('DescribeAdminAreas');
        $method->setProcessMode(Application_API_Method::PROCESS_MODE_RETURN);
        $method->process();
        
        $this->durationStop();
    }
}