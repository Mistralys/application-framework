<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses;

use Application\NewsCentral\NewsEntry;
use AppUtils\Microtime;

abstract class NewsTestCase extends ApplicationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }

    protected function assertDatesHaveBeenSet(NewsEntry $entry) : void
    {
        $checkDateFormat = 'Y-m-d H:i';
        $date = Microtime::createNow()->format($checkDateFormat);

        $this->assertSame($date, $entry->getDateCreated()->format($checkDateFormat));
        $this->assertSame($date, $entry->getDateModified()->format($checkDateFormat));
    }
}
