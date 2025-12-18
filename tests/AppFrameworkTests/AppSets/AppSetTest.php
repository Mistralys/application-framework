<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppSets;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application_Sets_Set;

final class AppSetTest extends ApplicationTestCase
{
    public function test_recognizeByURLName() : void
    {
        $set = Application_Sets_Set::fromArray(array(
            Application_Sets_Set::KEY_ID => 'test',
            Application_Sets_Set::KEY_DEFAULT_AREA => ManageNewsArea::URL_NAME,
            Application_Sets_Set::KEY_ENABLED => array()
        ));

        $this->assertSame(ManageNewsArea::URL_NAME, $set->getDefaultArea()->getURLName());
    }

    public function test_recognizeByClassName() : void
    {
        $name = getClassTypeName(ManageNewsArea::class);

        $set = Application_Sets_Set::fromArray(array(
            Application_Sets_Set::KEY_ID => 'test',
            Application_Sets_Set::KEY_DEFAULT_AREA => $name,
            Application_Sets_Set::KEY_ENABLED => array()
        ));

        $this->assertSame(ManageNewsArea::class, get_class($set->getDefaultArea()));
    }
}
