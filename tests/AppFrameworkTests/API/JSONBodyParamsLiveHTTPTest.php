<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIMethodInterface;
use Application\API\Traits\JSONRequestInterface;
use Application\ConfigSettings\AppConfig;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\ConvertHelper\JSONConverter;
use PHPUnit\Framework\Attributes\Group;
use TestDriver\API\TestJSONBodyParamsMethod;

/**
 * Live-HTTP regression test: dispatches {@see TestJSONBodyParamsMethod} with
 * a real JSON request body through the real `tests/application/api/index.php`
 * entry point (not `processReturn()`), proving JSON-body parameter
 * resolution (see {@see \Application\API\Parameters\ValueSource\JSONBodyValueSource})
 * works identically over real HTTP dispatch — body values win, and a key
 * absent from the body falls back to the query string — per AC-18 (the
 * `payload`/base64 portion of AC-18 is covered once a later work package
 * adds `Base64FileParameter` to this fixture).
 *
 * Also covers AC-07: the `process()`-path request body ceiling, proving the
 * {@see \Application\API\BaseMethods\BaseAPIMethod::getRequestBody()} wiring
 * added for the ceiling behaves identically over real HTTP dispatch to the
 * in-process {@see \AppFrameworkTests\API\RequestBodyCeilingTest} coverage —
 * an unparseable body (the `process()`-path gap this plan closes) and an
 * oversized body are each rejected with the correct HTTP status and error
 * code, never HTTP 200 or an HTML page.
 *
 * Requires a running webserver reachable at `APP_URL` — excluded from the
 * default suite (see the `live-http` group exclusion in `phpunit.xml`).
 */
#[Group('live-http')]
final class JSONBodyParamsLiveHTTPTest extends ApplicationTestCase
{
    public function test_jsonBodyResolvesWithQueryStringFallbackOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestJSONBodyParamsMethod::METHOD_NAME,
            // 'flag' is deliberately omitted from the body below, so this
            // query-string value must be the one that is resolved for it.
            TestJSONBodyParamsMethod::PARAM_FLAG => 'true',
        ));

        $body = JSONConverter::var2json(array(
            TestJSONBodyParamsMethod::PARAM_LABEL => 'from-body',
            TestJSONBodyParamsMethod::PARAM_COUNT => 7,
            TestJSONBodyParamsMethod::PARAM_META => array('nested' => 'value'),
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->assertIsString($response, 'The request must have received a response body.');
        $this->assertSame(200, $httpCode, 'A valid JSON body request must succeed.');
        $this->assertStringContainsString('application/json', $contentType);

        $data = JSONConverter::json2var($response);
        $this->assertIsArray($data);

        $payload = $data['data'] ?? array();

        $this->assertSame('from-body', $payload[TestJSONBodyParamsMethod::PARAM_LABEL] ?? null);
        $this->assertSame(7, $payload[TestJSONBodyParamsMethod::PARAM_COUNT] ?? null);
        $this->assertSame(array('nested' => 'value'), $payload[TestJSONBodyParamsMethod::PARAM_META] ?? null);

        // Absent from the body: resolved from the query string fallback.
        $this->assertTrue($payload[TestJSONBodyParamsMethod::PARAM_FLAG] ?? null);
    }

    /**
     * Proves the `process()`-path unparseable-body gap this plan closes:
     * before this work package, only `processReturn()` was covered by
     * {@see \AppFrameworkTests\API\Parameters\JSONBodyParamResolutionTest}.
     */
    public function test_unparseableBodyIsRejectedOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestJSONBodyParamsMethod::METHOD_NAME,
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'not valid json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->assertIsString($response, 'The request must have received a response body.');
        $this->assertSame(400, $httpCode, 'An unparseable body must produce HTTP 400, never HTTP 200.');
        $this->assertStringContainsString(
            'application/json',
            $contentType,
            'The response must be JSON, never an HTML error page.'
        );

        $data = JSONConverter::json2var($response);
        $this->assertIsArray($data);
        $this->assertSame(JSONRequestInterface::ERROR_FAILED_TO_READ_INPUT, $data['code'] ?? null);

        // This test suite's environment is permanently a development
        // environment, so the unparseable-body echo is expected.
        $jsonRequest = $data['data'][JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA] ?? null;
        $this->assertIsArray($jsonRequest);
        $this->assertSame(
            'not valid json',
            $jsonRequest[JSONRequestInterface::RESPONSE_KEY_ERROR_UNPARSEABLE_BODY] ?? null
        );
    }

    /**
     * Proves the body-size ceiling is enforced over the real `process()`
     * dispatch path, not just `processReturn()` — see
     * {@see \AppFrameworkTests\API\RequestBodyCeilingTest} for the in-process
     * coverage of every `RequestBodyException` conversion branch. The
     * ceiling cannot be lowered for this test (boot settings are fixed once
     * the application has booted), so this sends a body genuinely over the
     * booted default; whether PHP's own `post_max_size` discards it first or
     * the configured ceiling itself rejects it, both causes resolve to the
     * same client-facing code.
     */
    public function test_oversizedBodyIsRejectedOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestJSONBodyParamsMethod::METHOD_NAME,
        ));

        $oversizedBody = str_repeat('a', AppConfig::getAPIMaxRequestBodySize() + 1);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $oversizedBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->assertIsString($response, 'The request must have received a response body.');
        $this->assertSame(413, $httpCode, 'An oversized body must produce HTTP 413, never HTTP 200.');
        $this->assertStringContainsString(
            'application/json',
            $contentType,
            'The response must be JSON, never an HTML error page.'
        );

        $data = JSONConverter::json2var($response);
        $this->assertIsArray($data);
        $this->assertSame(JSONRequestInterface::ERROR_REQUEST_BODY_TOO_LARGE, $data['code'] ?? null);
    }
}
