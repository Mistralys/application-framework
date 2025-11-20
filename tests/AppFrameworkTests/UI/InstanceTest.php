<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI;

final class InstanceTest extends ApplicationTestCase
{
    public function test_selectInstance() : void
    {
        $main = UI::getInstance();
        $new = UI::selectInstance('test');

        $this->assertSame('test', UI::getInstance()->getInstanceKey());

        $main->addJavascriptHeadStatement("alert('main');");
        $new->addJavascriptHeadStatement("alert('new');");

        $this->assertNotEquals($main->getInstanceKey(), $new->getInstanceKey());

        $this->assertStringContainsString("alert('main');", $main->renderHeadIncludes());
        $this->assertStringContainsString("alert('new');", $new->renderHeadIncludes());

        $this->assertStringNotContainsString("alert('new');", $main->renderHeadIncludes());
        $this->assertStringNotContainsString("alert('main');", $new->renderHeadIncludes());
    }

    public function test_switchInstances() : void
    {
        UI::selectInstance('test1');
        UI::selectInstance('test2');
        UI::selectPreviousInstance();

        $this->assertSame('test1', UI::getInstance()->getInstanceKey());
    }

    public function test_switchToDefaultInstance() : void
    {
        $main = UI::getInstance();

        UI::selectInstance('test1');
        UI::selectDefaultInstance();

        $this->assertSame($main->getInstanceKey(), UI::getInstance()->getInstanceKey());
    }
}
