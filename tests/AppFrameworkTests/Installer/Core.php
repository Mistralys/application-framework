<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Application;

final class Installer_CoreTest extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }

    /**
     * The installer must load tasks from the framework and
     * the application together.
     */
    public function test_loadTasks(): void
    {
        $this->startTest('Create installer');

        $installer = Application::createInstaller();
        $tasks = $installer->getTasks();
        $ids = $installer->getTaskIDs();

        $this->assertGreaterThan(0, count($tasks));
        $this->assertContains('InitSystemUsers', $ids);
        $this->assertContains('DriverSpecific', $ids);
    }

    public function test_getTaskByID() : void
    {
        $this->startTest('Get a task by its ID');

        $installer = Application::createInstaller();

        $task = $installer->getTaskByID('DriverSpecific');

        $this->assertInstanceOf(Application_Installer_Task::class, $task);
        $this->assertEquals('DriverSpecific', $task->getID());
    }

    public function test_getTaskByID_notExists() : void
    {
        $this->startTest('Exception when task does not exist');

        $installer = Application::createInstaller();

        $this->expectException(Application_Exception::class);

        $installer->getTaskByID('UnknownTestsuiteTask');
    }
}
