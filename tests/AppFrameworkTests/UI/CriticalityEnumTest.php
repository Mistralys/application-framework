<?php

declare(strict_types=1);

namespace testsuites\UI;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI\CriticalityEnum;

final class CriticalityEnumTest extends ApplicationTestCase
{
    public function test_isValidType() : void
    {
        $this->assertTrue(CriticalityEnum::isValidValue('success'));
        $this->assertFalse(CriticalityEnum::isValidValue('some unknown value'));
    }

    public function test_requireValidValue() : void
    {
        $this->expectExceptionCode(ENUM_ERROR_INVALID_VALUE);

        CriticalityEnum::requireValidValue('some unknown value');
    }

    /**
     * Values are sorted alphabetically by their names.
     */
    public function test_getValues() : void
    {
        $this->assertSame(
            array(
                CriticalityEnum::DANGEROUS,
                CriticalityEnum::INACTIVE,
                CriticalityEnum::INFO,
                CriticalityEnum::INVERSE,
                CriticalityEnum::SUCCESS,
                CriticalityEnum::WARNING
            ),
            array_values(CriticalityEnum::getValues())
        );
    }
}
