<?php

declare(strict_types=1);

namespace AppFrameworkTests\DeploymentRegistry;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\DeploymentRegistry\Tasks\ClearClassCacheTask;
use Application\DeploymentRegistry\Tasks\StoreDeploymentInfoTask;
use Application\DeploymentRegistry\Tasks\WriteLocalizationFilesTask;
use TestDriver\ClassFactory;

final class ClassLoadingTests extends ApplicationTestCase
{
    public function test_classLoading() : void
    {
        $classes = ClassFactory::createDeploymentRegistry()->getTaskClasses();

        $this->assertContains(StoreDeploymentInfoTask::class, $classes);
        $this->assertContains(WriteLocalizationFilesTask::class, $classes);
        $this->assertContains(ClearClassCacheTask::class, $classes);
    }

    public function test_getByID() : void
    {
        $registry = ClassFactory::createDeploymentRegistry();

        $this->assertInstanceOf(StoreDeploymentInfoTask::class, $registry->getByID(StoreDeploymentInfoTask::TASK_NAME));
        $this->assertInstanceOf(WriteLocalizationFilesTask::class, $registry->getByID(WriteLocalizationFilesTask::TASK_NAME));
        $this->assertInstanceOf(ClearClassCacheTask::class, $registry->getByID(ClearClassCacheTask::TASK_NAME));
    }
}
