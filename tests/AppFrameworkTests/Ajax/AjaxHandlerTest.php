<?php

declare(strict_types=1);

namespace AppFrameworkTests\Ajax;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\AjaxMethods\AddFeedbackMethod;
use Application\AjaxMethods\NoAjaxHandlerFoundMethod;
use Application_AjaxHandler;
use TestDriver\AjaxMethods\AjaxGetTestJSON;

final class AjaxHandlerTest extends ApplicationTestCase
{
    public function test_getMethods() : void
    {
        $names = $this->getHandler()->getMethodNames();

        $this->assertNotEmpty($names);
        $this->assertContains(AddFeedbackMethod::METHOD_NAME, $names);
        $this->assertContains(NoAjaxHandlerFoundMethod::METHOD_NAME, $names);
        $this->assertContains(AjaxGetTestJSON::METHOD_NAME, $names);
    }

    public function test_getByName() : void
    {
        $this->assertInstanceOf(
            AddFeedbackMethod::class,
            $this->getHandler()->getMethodByName(AddFeedbackMethod::METHOD_NAME)
        );
    }

    public function test_getByNameNotExists() : void
    {
        $this->assertNull($this->getHandler()->getMethodByName('NotExists'));
    }

    public function test_requireByName() : void
    {
        $this->assertInstanceOf(
            AddFeedbackMethod::class,
            $this->getHandler()->requireMethodByName(AddFeedbackMethod::METHOD_NAME)
        );
    }

    public function test_requireByNameExceptionIfNotExists() : void
    {
        $this->expectExceptionCode(Application_AjaxHandler::ERROR_NO_SUCH_METHOD);

        $this->getHandler()->requireMethodByName('NotExists');
    }

    private function getHandler() : Application_AjaxHandler
    {
        return AppFactory::createDriver()->getAjaxHandler();
    }
}
