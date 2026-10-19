<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\Removed\RemovedParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RemovedParameterTest extends APITestCase
{
    // region: Name validation

    /**
     * @return array<int,array<int,string>>
     */
    public static function provideInvalidNames() : array
    {
        return array(
            array('legacy.flag'),
            array('legacy flag'),
            array('legacy[x'),
            array('legacy[]'),
            array("legacy\0flag"),
        );
    }

    #[DataProvider('provideInvalidNames')]
    public function test_constructorRejectsInvalidName(string $name) : void
    {
        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG);

        new RemovedParameter($name);
    }

    #[DataProvider('provideInvalidNames')]
    public function test_setReplacementRejectsInvalidName(string $name) : void
    {
        $param = new RemovedParameter('legacy_flag');

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG);

        $param->setReplacement($name);
    }

    /**
     * @return array<int,array<int,string>>
     */
    public static function provideValidNames() : array
    {
        return array(
            array('legacy_flag'),
            array('legacy-flag'),
            array('a]b'),
        );
    }

    #[DataProvider('provideValidNames')]
    public function test_constructorAcceptsValidName(string $name) : void
    {
        $param = new RemovedParameter($name);

        $this->assertSame($name, $param->getName());
    }

    #[DataProvider('provideValidNames')]
    public function test_setReplacementAcceptsValidName(string $name) : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setReplacement($name);

        $this->assertSame($name, $param->getReplacement());
    }

    // endregion

    // region: Error message composition

    public function test_errorMessageWithNameOnly() : void
    {
        $param = new RemovedParameter('legacy_flag');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('legacy_flag', $message);
        $this->assertStringNotContainsString('instead', $message);
        $this->assertStringNotContainsString('since', $message);
    }

    public function test_errorMessageWithReplacement() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setReplacement('new_flag');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('legacy_flag', $message);
        $this->assertStringContainsString('new_flag', $message);
        $this->assertStringContainsString('instead', $message);
    }

    public function test_errorMessageWithRemovedSince() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setRemovedSince('2.5.0');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('2.5.0', $message);
    }

    public function test_errorMessageWithNote() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setNote('See the migration guide.');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('See the migration guide.', $message);
    }

    public function test_errorMessageWithAllFields() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setReplacement('new_flag');
        $param->setRemovedSince('2.5.0');
        $param->setNote('See the migration guide.');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('legacy_flag', $message);
        $this->assertStringContainsString('new_flag', $message);
        $this->assertStringContainsString('2.5.0', $message);
        $this->assertStringContainsString('See the migration guide.', $message);
    }

    public function test_errorMessageWithLiteralPercentInNoteIsVerbatim() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setNote('Usage dropped by 50%, format was %s.');

        $message = $param->getErrorMessage();

        $this->assertStringContainsString('Usage dropped by 50%, format was %s.', $message);
    }

    // endregion

    // region: toArray()

    public function test_toArrayWithLiteralPercentInNoteIsVerbatim() : void
    {
        $param = new RemovedParameter('legacy_flag');
        $param->setReplacement('new_flag');
        $param->setRemovedSince('2.5.0');
        $param->setNote('Usage dropped by 50%, format was %s.');

        $data = $param->toArray();

        $this->assertSame('legacy_flag', $data[RemovedParameter::KEY_NAME]);
        $this->assertSame('new_flag', $data[RemovedParameter::KEY_REPLACEMENT]);
        $this->assertSame('2.5.0', $data[RemovedParameter::KEY_REMOVED_SINCE]);
        $this->assertSame('Usage dropped by 50%, format was %s.', $data[RemovedParameter::KEY_NOTE]);
    }

    public function test_toArrayWithOnlyNameHasNullOptionalFields() : void
    {
        $param = new RemovedParameter('legacy_flag');

        $data = $param->toArray();

        $this->assertNull($data[RemovedParameter::KEY_REPLACEMENT]);
        $this->assertNull($data[RemovedParameter::KEY_REMOVED_SINCE]);
        $this->assertNull($data[RemovedParameter::KEY_NOTE]);
    }

    // endregion
}
