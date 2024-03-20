<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Mistralys\AppFrameworkTests\TestClasses\Traits\RevisionableTestTrait;

abstract class RevisionableTestCase extends ApplicationTestCase
{
    use RevisionableTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRevisionableTest();
    }
}
