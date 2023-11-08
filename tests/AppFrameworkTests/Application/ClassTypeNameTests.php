<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Application;

use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class ClassTypeNameTests extends ApplicationTestCase
{
    public function test_simpleName() : void
    {
        $this->assertSame(
            'Simple',
            getClassTypeName('Simple')
        );
    }

    public function test_legacyName() : void
    {
        $this->assertSame(
            'Name',
            getClassTypeName('Legacy_Class_Name')
        );
    }

    public function test_namespacedName() : void
    {
        $this->assertSame(
            'ClassName',
            getClassTypeName('Namespace\SubPath\ClassName')
        );
    }

    public function test_namespacedLegacyName() : void
    {
        $this->assertSame(
            'ClassName',
            getClassTypeName('Namespace\SubPath\Legacy_Class_ClassName')
        );
    }
}
