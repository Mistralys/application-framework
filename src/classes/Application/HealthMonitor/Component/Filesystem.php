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
class Application_HealthMonitor_Component_Filesystem extends Application_HealthMonitor_Component
{
    public function getName()
    {
        return 'Filesystem';
    }

    public function getDescription()
    {
        return sprintf('Underlying %1$s file system access.', $this->driver->getAppNameShort());
    }

    public function getYellowPagesURL()
    {
        return '';
    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    public function getFolders()
    {
        return array(
            array(
                'path' => APP_ROOT.'/storage',
                'required' => true,
                'writable' => true
            ),
            array(
                'path' => APP_ROOT.'/storage/temp',
                'required' => false,
                'writable' => true
            ),
            array(
                'path' => APP_ROOT.'/logs/error',
                'required' => false,
                'writable' => true
            ),
            array(
                'path' => APP_ROOT.'/logs/general',
                'required' => false,
                'writable' => true
            )
        );
    }
    
    public function collectData()
    {
        $folders = $this->getFolders();
        
        try {
            $this->durationStart();

            foreach($folders as $def) {
                $path = str_replace('\\', '/', $def['path']); // normalize the path
                $exists = file_exists($path);
                $folder = basename($path);
                $parts = explode('/', $path);
                array_pop($parts);
                $parent = implode('/', $parts); // the parent folder
                
                if($def['required'] && !$exists) {
                    $this->setError(sprintf(
                        'The folder %1$s is required but does not exist.',
                        $path
                    ));
                    break;
                }
                
                if($def['writable']) {
                    if($exists) {
                        if(!is_writable($path)) {
                            $this->setError(sprintf(
                                'The folder %1$s exists, but is not writable.',
                                $folder
                            ));
                        }
                    } else {
                        if(!is_writable($parent)) {
                            $this->setError(sprintf(
                                'The folder %1$s must be createable, but its parent folder %2$s is not writable.',
                                $folder,
                                $parent
                            ));
                        }
                    }
                }
            }

            $this->durationStop();

        } catch (Exception $e) {
            $this->setError('Exception while trying to check the filesystem.');
            $this->setException($e);
        }
    }
}