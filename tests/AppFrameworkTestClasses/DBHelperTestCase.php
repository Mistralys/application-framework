<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppFrameworkTestClasses\Traits\DBHelperTestTrait;

/**
 * @package Application
 * @subpackage UnitTests
 */
abstract class DBHelperTestCase extends ApplicationTestCase implements DBHelperTestInterface
{
    use DBHelperTestTrait;
}
