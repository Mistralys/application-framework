<?php

declare(strict_types=1);

namespace AppFrameworkTests\SourceFolders;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\API\Methods\DescribeAdminAreasAPI;
use Application\API\APIManager;
use Application\AppFactory;
use Application\DeploymentRegistry\Tasks\ClearClassCacheTask;
use Application_AjaxMethods_GetWhatsnew;
use TestDriver\AjaxMethods\AjaxGetTestJSON;
use TestDriver\API\TestDryRunMethod;
use TestDriver\DeploymentTasks\TestDeploymentTask;
use TestDriver\ExternalSources\Ajax\ExternalAJAXMethod;
use TestDriver\ExternalSources\API\ExternalAPIMethod;
use TestDriver\ExternalSources\DeploymentTasks\ExternalDeploymentTask;

final class ClassLoadingTest extends ApplicationTestCase
{
    public function test_APILoading() : void
    {
        $classes = APIManager::getInstance()->getMethodCollection()->getClassNames();

        $this->assertContains(DescribeAdminAreasAPI::class, $classes, 'Framework-internal API method class not found in loaded classes.');
        $this->assertContains(TestDryRunMethod::class, $classes, 'TestDriver API method class not found in loaded classes.');
        $this->assertContains(ExternalAPIMethod::class, $classes, 'External source API method class not found in loaded classes.');
    }

    public function test_AJAXLoading() : void
    {
        $names = AppFactory::createDriver()->getAjaxHandler()->getMethodNames();

        $this->assertContains(Application_AjaxMethods_GetWhatsnew::METHOD_NAME, $names, 'Framework-internal AJAX method not found in loaded classes.');
        $this->assertContains(AjaxGetTestJSON::METHOD_NAME, $names, 'TestDriver AJAX method not found in loaded classes.');
        $this->assertContains(ExternalAJAXMethod::METHOD_NAME, $names, 'External source AJAX method class not found in loaded classes.');
    }

    public function test_externalDeploymentTaskLoading() : void
    {
        $names = AppFactory::createDeploymentRegistry()->getIDs();

        $this->assertContains(ClearClassCacheTask::TASK_NAME, $names, 'Framework-internal deployment task not found in loaded classes.');
        $this->assertContains(TestDeploymentTask::TASK_ID, $names, 'TestDriver deployment task not found in loaded classes.');
        $this->assertContains(ExternalDeploymentTask::TASK_ID, $names, 'External source deployment task not found in loaded classes.');
    }
}
