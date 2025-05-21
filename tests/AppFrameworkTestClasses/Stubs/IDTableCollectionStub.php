<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application_CustomProperties;
use TestDriver\TestDBRecords\TestDBCollection;

class IDTableCollectionStub extends TestDBCollection
{
    protected function init(): void
    {
        parent::init();

        $this->setIDTable(
            Application_CustomProperties::TABLE_NAME,
            Application_CustomProperties::PRIMARY_NAME
        );
    }
}
