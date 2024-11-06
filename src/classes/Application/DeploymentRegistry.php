<?php
/**
 * @package Application
 * @subpackage DeploymentRegistry
 */

declare(strict_types=1);

namespace Application;

use Application\Admin\Area\Devel\BaseDeploymentHistoryScreen;
use Application\DeploymentRegistry\DeploymentTaskInterface;
use Application\DeploymentRegistry\Tasks\WriteLocalizationFilesTask;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Admin_Area_Devel;
use Application_Driver;
use Application_Exception;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper\FolderInfo;
use Application\DeploymentRegistry\DeploymentInfo;

/**
 * The deployment registry is responsible for managing the deployment tasks,
 * as well as storing the deployment history.
 *
 * @package Application
 * @subpackage DeploymentRegistry
 *
 * @method DeploymentTaskInterface getByID(string $id)
 * @method DeploymentTaskInterface[] getAll()
 * @method DeploymentTaskInterface getDefault()
 */
class DeploymentRegistry extends BaseStringPrimaryCollection implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const SETTING_DEPLOYMENT_HISTORY = 'deployment_history';

    public const ERROR_VERSION_DOES_NOT_EXIST = 123901;

    /**
     * @var DeploymentInfo[]|null
     */
    private ?array $historyCache = null;

    /**
     * @return DeploymentInfo[] From oldest to newest.
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

    public function getDefaultID(): string
    {
        return WriteLocalizationFilesTask::TASK_NAME;
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
        $this->logHeader('Registering deployment');

        $this->resetHistoryCache();

        $this->log('Processing [%s] tasks.', $this->countRecords());

        foreach($this->getAll() as $task) {
            $task->process();
        }

        return $this;
    }

    protected function registerItems(): void
    {
        foreach($this->getTaskClasses() as $class)
        {
            $task = $this->createTask($class);

            $this->registerItem($task);

            $this->log('Registered task [%s].', $class);
        }
    }

    private function createTask(string $class) : DeploymentTaskInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            DeploymentTaskInterface::class,
            new $class()
        );
    }

    /**
     * @return FolderInfo[]
     */
    public function getTaskFolders() : array
    {
        return array(
            FolderInfo::factory(Application_Driver::getInstance()->getClassesFolder().'/DeploymentTasks'),
            FolderInfo::factory(__DIR__.'/DeploymentRegistry/Tasks'),
        );
    }

    /**
     * @return class-string<DeploymentTaskInterface>[]
     */
    public function getTaskClasses() : array
    {
        $folders = $this->getTaskFolders();

        $tasks = array();

        foreach($folders as $folder)
        {
            if(!$folder->exists()) {
                continue;
            }

            $classes = ClassHelper::findClassesInFolder($folder, true, DeploymentTaskInterface::class);

            foreach ($classes as $class) {
                $tasks[] = $class->getNameNS();
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

    /**
     * Clears the entire deployment history.
     * @return void
     */
    public function clearHistory() : void
    {
        $this->log('Clearing the deployment history.');

        Application_Driver::createSettings()->delete(self::SETTING_DEPLOYMENT_HISTORY);
        $this->resetHistoryCache();
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
