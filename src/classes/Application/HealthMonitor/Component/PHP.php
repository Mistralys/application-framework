<?php
/**
 * File containing the {@link Application_HealthMonitor_Component_PHP} class.
 * @package Application
 * @subpackage HealthMonitor
 */

/**
 * Checks the PHP version and required extensions.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_HealthMonitor_Component_PHP extends Application_HealthMonitor_Component
{
    const float MINIMUM_PHP_VERSION = 5.6;

    public function getName()
    {
        return 'PHP Installation';
    }

    public function getDescription()
    {
        return sprintf('Configuration of the PHP installation running %1$s.', $this->driver->getAppNameShort());
    }

    public function getYellowPagesURL()
    {
        return '';
    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    public function collectData()
    {
        $version = (int)substr(phpversion(), 0, 3) * 1;
        if ($version < self::MINIMUM_PHP_VERSION) {
            $this->setError(sprintf('PHP Version is lower than %1$s.', self::MINIMUM_PHP_VERSION));
        }

        if (!class_exists('DOMDocument')) {
            $this->setError('XML DOM methods are not available');
        }

        if (!function_exists('curl_init')) {
            $this->setWarning('CURL is not available');
        }
        
        if(!function_exists('ldap_connect')) {
            $this->setWarning('LDAP is not available');
        }
    }
}
