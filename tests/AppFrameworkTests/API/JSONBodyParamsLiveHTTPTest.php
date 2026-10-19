<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIMethodInterface;
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
}
