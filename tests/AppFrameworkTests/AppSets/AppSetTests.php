<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppSets;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application_Sets_Set;
use TestDriver\Area\NewsScreen;

final class AppSetTests extends ApplicationTestCase
{
    public function test_recognizeByURLName() : void
    {
        $set = Application_Sets_Set::fromArray(array(
            Application_Sets_Set::KEY_ID => 'test',
            Application_Sets_Set::KEY_DEFAULT_AREA => NewsScreen::URL_NAME,
            Application_Sets_Set::KEY_ENABLED => array()
        ));

        $this->assertSame(NewsScreen::URL_NAME, $set->getDefaultArea()->getURLName());
    }

    public function test_recognizeByClassName() : void
    {
        $name = getClassTypeName(NewsScreen::class);

        $set = Application_Sets_Set::fromArray(array(
            Application_Sets_Set::KEY_ID => 'test',
            Application_Sets_Set::KEY_DEFAULT_AREA => $name,
            Application_Sets_Set::KEY_ENABLED => array()
        ));

        $this->assertSame(NewsScreen::class, get_class($set->getDefaultArea()));
    }
}
