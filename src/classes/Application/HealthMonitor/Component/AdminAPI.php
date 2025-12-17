<?php
/**
 * @package Application
 * @subpackage HealthMonitor
 */

declare(strict_types=1);

use Application\Admin\Index\API\Methods\DescribeAdminAreasAPI;

/**
 * Checks the database connectivity and speed.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_HealthMonitor_Component_AdminAPI extends Application_HealthMonitor_Component
{
    public function getName() : string
    {
        return 'UI layer';
    }

    public function getDescription() : string
    {
        return 'Integrity of the UI layer, accessibility of all screens.';
    }

    public function getYellowPagesURL() : string
    {
        return '';
    }

    public function getSeverity() : string
    {
        return self::SEVERITY_BLOCKER;
    }

    public function collectData() : void
    {
        $this->durationStart();

        // This only has to work without exceptions.
        Application::createAPI()
            ->loadMethod(DescribeAdminAreasAPI::class)
            ->processReturn();
        
        $this->durationStop();
    }
}