<?php
/**
 * @package Application
 * @subpackage HealthMonitor
 */

declare(strict_types=1);

/**
 * Checks the database connectivity and speed.
 *
 * @package Application
 * @subpackage HealthMonitor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_HealthMonitor_Component_Database extends Application_HealthMonitor_Component
{
    public function getName() : string
    {
        return 'Database';
    }

    public function getDescription() : string
    {
        return sprintf('Core database containing all %1$s tables and data.', $this->driver->getAppNameShort());
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
        if (!class_exists('PDO')) {
            $this->setError('The PDO extension is not present.');

            return;
        }

        try
        {
            $this->durationStart();

            DBHelper::init();

            $data = DBHelper::fetch(
                "SELECT
                    *
                FROM
                    `known_users`
                WHERE
                    `user_id`=1"
            );

            $this->durationStop();

            if (!is_array($data) || !isset($data['user_id'])) {
                $this->setError('Could not retrieve system user from database.');
            }
        } catch (Exception $e) {
            $this->setError('Exception while trying to access the database.');
            $this->setException($e);
        }
    }
}