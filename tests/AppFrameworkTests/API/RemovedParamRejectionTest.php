<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Cache\APICacheManager;
use Application\API\ErrorResponsePayload;
use Application\API\ResponsePayload;
use Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestErrorResponseMethod;
use TestDriver\API\TestRemovedParamMethod;

/**
 * Lifecycle-level tests for the removed-parameter declaration and
 * rejection step (see {@see \Application\API\BaseMethods\BaseAPIMethod::addRemovedParam()}
 * and {@see \Application\API\BaseMethods\BaseAPIMethod::rejectRemovedParams()}),
 * using {@see TestRemovedParamMethod} as the fixture.
 *
 * NOTE: All assertions run through {@see \Application\API\BaseMethods\BaseAPIMethod::processReturn()}.
 * {@see \Application\API\BaseMethods\BaseAPIMethod::process()} executes the
 * exact same {@see \Application\API\BaseMethods\BaseAPIMethod::rejectRemovedParams()}
 * call inside the shared `_process()` boundary, but terminates the PHP
 * process via {@see \Application\Application::exit()} on the live HTTP
 * path — which cannot be exercised from within a PHPUnit process. The
 * `getHttpStatusCode()` and `getErrorCode()` assertions below are exactly
 * what `process()` would send as the HTTP status line and JSON body.
 */
final class RemovedParamRejectionTest extends APITestCase
{
    protected function tearDown() : void
    {
        parent::tearDown();

        APICacheManager::clearAll();
    }

    private function createMethod() : TestRemovedParamMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestRemovedParamMethod::METHOD_NAME;

        return new TestRemovedParamMethod(APIManager::getInstance());
    }

    // region: Standard code and HTTP status

    public function test_removedParamIsRejectedWithStandardCodeAndStatus() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
        $this->assertSame(400, $data->getHttpStatusCode());
    }

    public function test_removedParamWithEmptyValueIsStillRejected() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = '';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
        $this->assertSame(400, $data->getHttpStatusCode());
    }

    // endregion

    // region: Message composition

    public function test_errorMessageNamesReplacementRemovedSinceAndNoteVerbatim() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());

        $message = $data->getErrorMessage();

        $this->assertStringContainsString('['.TestRemovedParamMethod::PARAM_OLD_NAME.']', $message);
        $this->assertStringContainsString('['.TestRemovedParamMethod::PARAM_NEW_NAME.']', $message);
        $this->assertStringContainsString(TestRemovedParamMethod::VERSION_2_0, $message);

        // The note's literal "%"/"%s" sequences must appear verbatim — never
        // interpreted as a sprintf() format string — and the response must
        // still be the stable removed-parameter code, never 183008.
        $this->assertStringContainsString(TestRemovedParamMethod::OLD_NAME_NOTE, $message);
        $this->assertNotSame(APIMethodInterface::ERROR_UNEXPECTED_THROWABLE, $data->getErrorCode());
    }

    public function test_errorMessageForParamWithoutReplacementOmitsOptionalParts() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_LEGACY_FLAG] = 'anything';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $message = $data->getErrorMessage();

        $this->assertStringContainsString('['.TestRemovedParamMethod::PARAM_LEGACY_FLAG.']', $message);
        $this->assertStringNotContainsString('instead', $message);
    }

    // endregion

    // region: Error data shape

    public function test_errorDataContainsRemovedParametersWithoutSubmittedValue() : void
    {
        $submittedValue = 'super-secret-legacy-value';

        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = $submittedValue;

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);

        $errorData = $data->getErrorData();

        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_REMOVED_PARAMETERS, $errorData);

        $removedParameters = $errorData[APIMethodInterface::RESPONSE_KEY_REMOVED_PARAMETERS];

        $this->assertCount(1, $removedParameters);
        $this->assertSame(TestRemovedParamMethod::PARAM_OLD_NAME, $removedParameters[0]['name']);
        $this->assertSame(TestRemovedParamMethod::PARAM_NEW_NAME, $removedParameters[0]['replacement']);
        $this->assertSame(TestRemovedParamMethod::VERSION_2_0, $removedParameters[0]['removedSince']);
        $this->assertSame(TestRemovedParamMethod::OLD_NAME_NOTE, $removedParameters[0]['note']);

        // The submitted value must never appear in the structured
        // `removedParameters` data itself — it only ever contains the
        // declaration metadata (name/replacement/removedSince/note), never
        // what the client actually sent. (The separate `requestData` channel
        // is a distinct, dev-environment-only passthrough and is out of
        // scope for this assertion.)
        $this->assertStringNotContainsString(
            $submittedValue,
            (string)json_encode($removedParameters)
        );
    }

    public function test_errorDataContainsOneEntryPerOffendingName() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';
        $_REQUEST[TestRemovedParamMethod::PARAM_LEGACY_FLAG] = 'legacy-flag-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $errorData = $data->getErrorData();
        $removedParameters = $errorData[APIMethodInterface::RESPONSE_KEY_REMOVED_PARAMETERS];

        $this->assertCount(2, $removedParameters);
    }

    // endregion

    // region: Pre-validation precedence

    public function test_removedParamTakesPrecedenceOverMissingRequiredReplacement() : void
    {
        // The required "newName" parameter is deliberately NOT submitted:
        // the rejection must still be 183009, never 183003.
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
        $this->assertNotSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $data->getErrorCode());
    }

    public function test_removedParamTakesPrecedenceOverInvalidAPIVersion() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_API_VERSION] = 'not-a-valid-version';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data->getErrorCode());
    }

    // endregion

    // region: apiVersion interaction without a removed parameter

    public function test_invalidAPIVersionAloneStillProducesStandardValidationError() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_API_VERSION] = 'not-a-valid-version';

        $method = $this->createMethod();
        $data = $method->processReturn();

        // No removed parameter was sent: behaviour is unaffected by this
        // WP, i.e. the pre-existing standard validation error still applies.
        $this->assertInstanceOf(ErrorResponsePayload::class, $data);
        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $data->getErrorCode());
    }

    // endregion

    // region: Cache bypass

    public function test_removedParamRejectionIsNeverServedFromOrWrittenToCache() : void
    {
        // First call: a valid request warms the cache.
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';

        $warmupMethod = $this->createMethod();
        $warmupResponse = $warmupMethod->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $warmupResponse);
        $this->assertSame(1, $warmupMethod->getCollectCount());

        // Second call: same cache key, but now also sends a removed
        // parameter — rejection must happen before the cache is consulted,
        // so collectResponseData() must never run for this call.
        $_REQUEST[TestRemovedParamMethod::PARAM_OLD_NAME] = 'legacy-value';

        $rejectedMethod = $this->createMethod();
        $rejectedResponse = $rejectedMethod->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $rejectedResponse);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $rejectedResponse->getErrorCode());
        $this->assertSame(0, $rejectedMethod->getCollectCount());
    }

    // endregion

    // region: Unaffected success path

    public function test_requestWithoutRemovedParamsSucceedsAsBefore() : void
    {
        $_REQUEST[TestRemovedParamMethod::PARAM_NEW_NAME] = 'value';

        $method = $this->createMethod();
        $data = $method->processReturn();

        $this->assertInstanceOf(ResponsePayload::class, $data);
        $this->assertSame('value', $data->getData()[TestRemovedParamMethod::KEY_NEW_NAME]);
    }

    // endregion

    // region: HTML method documentation (WP-004)

    /**
     * Renders {@see TestRemovedParamMethod}'s HTML documentation page
     * ({@see \Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl})
     * and asserts the "Removed parameters" section is present and names
     * the declared `oldName` parameter.
     *
     * FALLBACK NOTE: {@see \Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl::generateParamList()}
     * registers its data grid under the process-wide static {@see \UI_DataGrid::$ids}
     * registry (see `UI_DataGrid::__construct()`), which rejects a second grid with
     * the same ID for the remainder of the PHPUnit process — so a second full-page
     * render in the same test run (e.g. for {@see TestErrorResponseMethod}) throws
     * `Application_Exception` ("Duplicate datagrid ID"), confirmed empirically before
     * writing this test. The "section absent" half of this test therefore falls back
     * to the small, side-effect-free accessor the template's
     * `generateRemovedParamList()` guard itself branches on —
     * `manageParams()->getRemovedParams()->isEmpty()` — rather than a second render,
     * per the plan's documented fallback for this WP.
     */
    public function test_documentationRendersRemovedParamSection() : void
    {
        $withRemovedParams = new TestRemovedParamMethod(APIManager::getInstance());

        $page = $this->createUI()->getPage();

        $output = $page->createTemplate(APIMethodDetailTmpl::class)
            ->setVar(APIMethodDetailTmpl::PARAM_METHOD, $withRemovedParams)
            ->render();

        $this->assertStringContainsString('Removed parameters', $output);
        $this->assertStringContainsString(TestRemovedParamMethod::PARAM_OLD_NAME, $output);

        // Fallback for the "section absent" case — see FALLBACK NOTE above.
        $withoutRemovedParams = new TestErrorResponseMethod(APIManager::getInstance());
        $this->assertTrue($withoutRemovedParams->manageParams()->getRemovedParams()->isEmpty());
    }

    // endregion
}
