<?php

declare(strict_types=1);

namespace testsuites\Application;

use ApplicationTestCase;
use TestDriver;

class WhatsNewTests extends ApplicationTestCase
{
    public function test_create() : void
    {
        $new = TestDriver::createWhatsnew();

        $this->assertCount(1, $new->getVersions());
    }
}
