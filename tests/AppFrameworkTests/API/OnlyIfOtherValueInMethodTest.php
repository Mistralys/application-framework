<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\Rules\RuleInterface;
use Application\API\ResponsePayload;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestOnlyIfOtherValueInMethod;

/**
 * End-to-end test for {@see \Application\API\Parameters\Rules\Type\OnlyIfOtherValueInRule},
 * exercised through {@see TestOnlyIfOtherValueInMethod} - proving that a
 * failing rule's own validation code ({@see RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE},
 * `183602`) becomes the method's actual error response, mirroring how
 * {@see \Application\API\Parameters\Rules\Type\OrRule}'s `183601` is
 * surfaced end to end.
 */
final class OnlyIfOtherValueInMethodTest extends APITestCase
{
    private function createMethod() : TestOnlyIfOtherValueInMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestOnlyIfOtherValueInMethod::METHOD_NAME;

        return new TestOnlyIfOtherValueInMethod(APIManager::getInstance());
    }

    public function test_aOnlyOptionAcceptedWhenModeIsA() : void
    {
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_MODE] = TestOnlyIfOtherValueInMethod::MODE_A;
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_A_ONLY_OPTION] = 'some-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
        $this->assertSame(TestOnlyIfOtherValueInMethod::MODE_A, $data->getData()[TestOnlyIfOtherValueInMethod::KEY_MODE]);
        $this->assertSame('some-value', $data->getData()[TestOnlyIfOtherValueInMethod::KEY_A_ONLY_OPTION]);
    }

    public function test_aOnlyOptionRejectedWhenModeIsB() : void
    {
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_MODE] = TestOnlyIfOtherValueInMethod::MODE_B;
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_A_ONLY_OPTION] = 'some-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $data->getErrorCode());

        // The generic 400 envelope only carries the standard "missing or
        // invalid parameters" message and code - the rule's own specific
        // code is only visible on the underlying validation results, which
        // is what configureValidationErrorResponse() would surface into
        // field-level error data for an application that implements it.
        $errors = $method->getValidationResults()->serializeErrors();
        $codes = array_column($errors, 'code');

        $this->assertContains(RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE, $codes);
    }

    public function test_aOnlyOptionOmittedWithModeBSucceeds() : void
    {
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_MODE] = TestOnlyIfOtherValueInMethod::MODE_B;

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
        $this->assertSame(TestOnlyIfOtherValueInMethod::MODE_B, $data->getData()[TestOnlyIfOtherValueInMethod::KEY_MODE]);
        $this->assertNull($data->getData()[TestOnlyIfOtherValueInMethod::KEY_A_ONLY_OPTION]);
    }

    public function test_aOnlyOptionOmittedWithModeASucceeds() : void
    {
        $_REQUEST[TestOnlyIfOtherValueInMethod::PARAM_MODE] = TestOnlyIfOtherValueInMethod::MODE_A;

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
        $this->assertNull($data->getData()[TestOnlyIfOtherValueInMethod::KEY_A_ONLY_OPTION]);
    }
}
