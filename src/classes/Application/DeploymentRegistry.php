<?php

declare(strict_types=1);

namespace Application;

use Application\Admin\Area\Devel\BaseDeploymentHistoryScreen;
use Application\Driver\DriverException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Admin_Area_Devel;
use Application_Driver;
use Application_Exception;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime_Exception;
use JsonException;
use Application\DeploymentRegistry\BaseDeployTask;
use Application\DeploymentRegistry\DeploymentInfo;
use Application\DeploymentRegistry\Tasks\StoreDeploymentInfo;
use testsuites\DBHelper\RecordTests;

class DeploymentRegistry implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const SETTING_DEPLOYMENT_HISTORY = 'deployment_history';

    public const ERROR_VERSION_DOES_NOT_EXIST = 123901;

    /**
     * @var BaseDeployTask[]
     */
    private array $tasks = array();

    /**
     * @var DeploymentInfo[]|null
     */
    private ?array $historyCache = null;

    public function __construct()
    {
    }

    /**
     * @return DeploymentInfo[] From oldest to newest.
     *
     * @throws Microtime_Exception
     * @throws DriverException
     * @throws JsonException
     */
    public function getHistory() : array
    {
        if(isset($this->historyCache)) {
            return $this->historyCache;
        }

        $data = Application_Driver::createSettings()->getArray(self::SETTING_DEPLOYMENT_HISTORY);
        $result = array();

        foreach($data as $entry)
        {
            if(is_array($entry)) {
                $result[] = DeploymentInfo::fromArray($entry);
            }
        }

        $this->historyCache = $result;

        return $result;
    }

    public function getLastDeployment() : ?DeploymentInfo
    {
        $history = $this->getHistory();

        if(!empty($history)) {
            return array_pop($history);
        }

        return null;
    }

    private function resetHistoryCache() : void
    {
        $this->historyCache = null;
    }

    public function registerDeployment() : self
    {
        $this->resetHistoryCache();
        $this->loadTasks();

        foreach($this->tasks as $task) {
            $task->process();
        }

        return $this;
    }

    /**
     * Loads tasks, both from the framework-internal task
     * folder and the application-specific task folder.
     *
     * @return void
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws DriverException
     * @throws FileHelper_Exception
     */
    private function loadTasks() : void
    {
        $tasks = $this->getTaskClasses();

        $this->logHeader('Loading %s deployment tasks.', count($tasks));

        foreach($tasks as $def)
        {
            $this->log('Loading task [%s]', $def['id']);
            $class = $def['class'];

            $this->tasks[] = ClassHelper::requireObjectInstanceOf(
                BaseDeployTask::class,
                new $class()
            );
        }
    }

    /**
     * @return array<int,array{folder:string,classTemplate:string}>
     * @throws DriverException
     */
    public function getTaskFolders() : array
    {
        return array(
            array(
                'folder' => Application_Driver::getInstance()->getClassesFolder().'/DeploymentTasks',
                'classTemplate' => sprintf(
                    '\%s\DeploymentTasks\{ID}',
                    APP_CLASS_NAME
                )
            ),
            array(
                'folder' => __DIR__.'/DeploymentRegistry/Tasks',
                'classTemplate' => str_replace(ClassHelper::getClassTypeName(StoreDeploymentInfo::class), '{ID}', StoreDeploymentInfo::class)
            )
        );
    }

    /**
     * @return array<int,array{id:string,class:string}>
     * @throws DriverException
     * @throws FileHelper_Exception
     */
    public function getTaskClasses() : array
    {
        $sources = $this->getTaskFolders();

        $tasks = array();

        foreach($sources as $source)
        {
            if(!is_dir($source['folder'])) {
                continue;
            }

            $ids = FileHelper::createFileFinder($source['folder'])
                ->getPHPClassNames();

            foreach ($ids as $id)
            {
                $tasks[] = array(
                    'id' => $id,
                    'class' => str_replace('{ID}', $id,$source['classTemplate'])
                );
            }
        }

        return $tasks;
    }

    public function getAdminURLDeleteHistory(array $params=array()) : string
    {
         $params[BaseDeploymentHistoryScreen::REQUEST_PARAM_DELETE_HISTORY] = 'yes';

         return $this->getAdminURLHistory($params);
    }

    public function getAdminURLHistory(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseDeploymentHistoryScreen::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }

    public function getLogIdentifier(): string
    {
        return 'DeploymentRegistry';
    }

    public function clearHistory() : void
    {
        Application_Driver::createSettings()->delete(self::SETTING_DEPLOYMENT_HISTORY);
    }

    public function versionExists(string $version) : bool
    {
        $history = $this->getHistory();

        foreach($history as $item)
        {
            if($item->getVersion() === $version) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $version
     * @return DeploymentInfo
     *
     * @throws Application_Exception
     * @throws DriverException
     * @throws JsonException
     * @throws Microtime_Exception
     */
    public function getByVersion(string $version) : DeploymentInfo
    {
        $history = $this->getHistory();

        foreach($history as $item)
        {
            if($item->getVersion() === $version) {
                return $item;
            }
        }

        throw new Application_Exception(
            'Deployment version not found.',
            sprintf(
                'The version [%s] was not found in the deployment history.',
                $version
            ),
            self::ERROR_VERSION_DOES_NOT_EXIST
        );
    }
}
