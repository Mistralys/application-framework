<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\ImageMediaTestInterface;
use AppFrameworkTestClasses\Traits\ImageMediaTestTrait;

abstract class MediaTestCase extends ApplicationTestCase implements ImageMediaTestInterface
{
    use ImageMediaTestTrait;
}
