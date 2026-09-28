<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIMethodInterface;
use Application\API\Traits\JSONRequestInterface;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\ConvertHelper\JSONConverter;
use PHPUnit\Framework\Attributes\Group;
use TestDriver\API\TestJSONRequestRedactionMethod;

/**
 * Live-HTTP regression test: dispatches {@see TestJSONRequestRedactionMethod}
 * through the real `tests/application/api/index.php` entry point with an
 * actual JSON request body, proving the redaction boundary behaves
 * identically to the in-process {@see ErrorResponseTest} coverage when
 * exercised over real HTTP dispatch.
 *
 * NOTE: This test suite's own environment is permanently a development
 * environment (see {@see \TestDriver\Environments\LocalEnvironment::getType()}),
 * so a real HTTP request here can only exercise the devel-mode branch —
 * it cannot itself prove the production ("not devel") redaction outcome.
 * That branch is proven in-process via reflection in
 * {@see ErrorResponseTest::withDevelEnvironment()}, since no public API
 * exists to flip a live webserver's detected environment per-request.
 * This test instead proves that real dispatch produces the exact same
 * well-formed shape the in-process tests assert on, and that `addData()`
 * content is unaffected by the request-derived redaction channel.
 *
 * Requires a running webserver reachable at `APP_URL` — excluded from the
 * default suite (see the `live-http` group exclusion in `phpunit.xml`).
 */
#[Group('live-http')]
final class ErrorPayloadRedactionLiveHTTPTest extends ApplicationTestCase
{
    public function test_jsonRequestBodyEchoedInDevelEnvironmentOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestJSONRequestRedactionMethod::METHOD_NAME,
        ));

        $body = JSONConverter::var2json(array('secretField' => 'sensitive-value'));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->assertIsString($response, 'The request must have received a response body.');
        $this->assertSame(TestJSONRequestRedactionMethod::ERROR_CODE, JSONConverter::json2var($response)['code'] ?? null);
        $this->assertGreaterThanOrEqual(400, $httpCode, 'An always-failing method must never return HTTP 2xx.');

        $data = JSONConverter::json2var($response);
        $errorData = $data['data'] ?? array();

        // Devel environment: the parsed body is echoed under 'JSONRequest'.
        $this->assertArrayHasKey(JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA, $errorData);
        $this->assertSame(
            'sensitive-value',
            $errorData[JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA]['secretField'] ?? null
        );

        // requestData always carries the minimal method allowlist regardless of environment.
        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA, $errorData);
        $this->assertSame(
            TestJSONRequestRedactionMethod::METHOD_NAME,
            $errorData[APIMethodInterface::RESPONSE_KEY_ERROR_REQUEST_DATA][APIMethodInterface::REQUEST_PARAM_METHOD] ?? null
        );
    }
}
