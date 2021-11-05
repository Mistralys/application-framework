<?php
/**
 * File containing the class {@see Application_Installer}.
 *
 * @package Application
 * @subpackage Installer
 * @see Application_Installer
 */

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\OperationResult_Collection;

/**
 * Framework and application installer script, which is used
 * to set up the application on its first run, as well as for
 * any subsequent updates.
 *
 * NOTE: The installer is entirely idempotent, and as such,
 * can be run as many times as necessary without affecting
 * any settings already performed previously.
 *
 * The tasks to perform are loaded from the framework itself
 * under `classes/Application/Installer/Task`, as well as the
 * application under `classes/DriverName/Installer/Task`.
 *
 * @package Application
 * @subpackage Installer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Installer implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_INVALID_TASK_CLASS = 75801;
    public const ERROR_UNKNOWN_INSTALLER_TASK = 75802;

    const SOURCE_FRAMEWORK = 'framework';
    const SOURCE_APPLICATION = 'application';

    /**
     * @var Application_Installer_Task[]
     */
    private $tasks = array();

    /**
     * @var array<string,string>
     */
    private $taskFolders;

    public function __construct()
    {
        $this->taskFolders = array(
            self::SOURCE_FRAMEWORK => __DIR__.'/Installer/Task',
            self::SOURCE_APPLICATION => APP_ROOT.'/assets/classes/'.APP_CLASS_NAME.'/Installer/Task'
        );

        $this->loadTasks();
    }

    public function process() : OperationResult_Collection
    {
        $this->log('Starting the installation process.');

        DBHelper::requireTransaction('Run the application installer tasks.');

        $result = new OperationResult_Collection($this);

        foreach ($this->tasks as $task)
        {
            $result->addResult($task->process());
        }

        return $result;
    }

    private function loadTasks() : void
    {
        $this->log('Loading tasks from disk.');

        foreach($this->taskFolders as $source => $folder)
        {
            if(!is_dir($folder))
            {
                $this->log(sprintf('Tasks source folder not found at [%s], skipping.', $folder));
                continue;
            }

            $ids = $this->resolveFolderTasks($folder);

            $this->log(sprintf('Found [%s] tasks in source folder [%s].', count($ids), $folder));

            foreach ($ids as $id)
            {
                $this->tasks[] = $this->createTask($id, $source);
            }
        }

        $this->log(sprintf('Loaded [%s] install tasks.', count($this->tasks)));

        $this->sortTasks();
    }

    private function sortTasks() : void
    {
        // TODO: Sort tasks by their dependencies.
    }

    /**
     * @return Application_Installer_Task[]
     */
    public function getTasks() : array
    {
        return $this->tasks;
    }

    private function createTask(string $id, string $source) : Application_Installer_Task
    {
        $taskClass = $this->getTaskClass($id, $source);

        $task = new $taskClass($this);

        if($task instanceof Application_Installer_Task)
        {
            return $task;
        }

        throw new Application_Exception(
            'Invalid installer task class',
            sprintf(
                'The class [%s] does not extend the [%s] class.',
                $taskClass,
                Application_Installer_Task::class
            ),
            self::ERROR_INVALID_TASK_CLASS
        );
    }

    private function getTaskClass(string $id, string $source) : string
    {
        if($source === self::SOURCE_APPLICATION)
        {
            return APP_CLASS_NAME.'_Installer_Task_'.$id;
        }

        return 'Application_Installer_Task_'.$id;
    }

    public function getTaskIDs() : array
    {
        $result = array();

        foreach ($this->tasks as $task)
        {
            $result[] = $task->getID();
        }

        return $result;
    }

    private function resolveFolderTasks(string $folder) : array
    {
        return FileHelper::createFileFinder($folder)
            ->getPHPClassNames();
    }

    public function getLogIdentifier(): string
    {
        return 'AppInstaller';
    }

    public function getTaskByID(string $id) : Application_Installer_Task
    {
        foreach ($this->tasks as $task)
        {
            if($task->getID() === $id)
            {
                return $task;
            }
        }

        throw new Application_Exception(
            'Unknown installer task',
            sprintf(
                'The task [%s] does not exist in the installer.',
                $id
            ),
            self::ERROR_UNKNOWN_INSTALLER_TASK
        );
    }
}
