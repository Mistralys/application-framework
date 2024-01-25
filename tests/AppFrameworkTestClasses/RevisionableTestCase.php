<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;

abstract class RevisionableTestCase extends ApplicationTestCase
{
    use Traits\RevisionableTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRevisionableTest();
    }
}
