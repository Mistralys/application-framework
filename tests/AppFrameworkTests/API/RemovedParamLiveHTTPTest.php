<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIMethodInterface;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\ConvertHelper\JSONConverter;
use PHPUnit\Framework\Attributes\Group;
use TestDriver\API\TestRemovedParamMethod;

/**
 * Live-HTTP regression test: dispatches {@see TestRemovedParamMethod} with a
 * declared removed parameter set through the real `tests/application/api/index.php`
 * entry point (not `processReturn()`), proving the removed-parameter rejection
 * lifecycle step added to {@see \Application\API\BaseMethods\BaseAPIMethod}
 * also covers the real `process()` path — a request still sending a removed
 * name must be rejected with the stable standard error over real HTTP, never
 * succeed with HTTP 200 or leak an HTML page.
 *
 * Requires a running webserver reachable at `APP_URL` — excluded from the
 * default suite (see the `live-http` group exclusion in `phpunit.xml`).
 */
#[Group('live-http')]
final class RemovedParamLiveHTTPTest extends ApplicationTestCase
{
    public function test_removedParamProducesJSONErrorOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestRemovedParamMethod::METHOD_NAME,
            TestRemovedParamMethod::PARAM_NEW_NAME => 'value',
            TestRemovedParamMethod::PARAM_OLD_NAME => 'legacy-value',
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->assertIsString($body, 'The request must have received a response body.');
        $this->assertSame(400, $httpCode, 'A removed-parameter request must produce HTTP 400, never HTTP 200.');
        $this->assertStringContainsString(
            'application/json',
            $contentType,
            'The response must be JSON, never an HTML error page.'
        );

        $data = JSONConverter::json2var($body);

        $this->assertIsArray($data);
        $this->assertSame(APIMethodInterface::ERROR_REMOVED_PARAMETER_USED, $data['code'] ?? null);

        $removedParameters = $data['data'][APIMethodInterface::RESPONSE_KEY_REMOVED_PARAMETERS] ?? null;
        $this->assertIsArray($removedParameters);
        $this->assertNotEmpty($removedParameters);
        $this->assertSame('newName', $removedParameters[0]['replacement'] ?? null);
    }
}
