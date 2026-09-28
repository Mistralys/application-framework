<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIMethodInterface;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\ConvertHelper\JSONConverter;
use PHPUnit\Framework\Attributes\Group;
use TestDriver\API\TestUnexpectedThrowableMethod;

/**
 * Live-HTTP regression test: dispatches {@see TestUnexpectedThrowableMethod}
 * through the real `tests/application/api/index.php` entry point (not
 * `processReturn()`), proving the shared execution boundary added to
 * {@see \Application\API\BaseMethods\BaseAPIMethod} also covers the real
 * `process()` path — a pre-collector failure must never escape as an
 * HTML system-error page or succeed with HTTP 200.
 *
 * Requires a running webserver reachable at `APP_URL` — excluded from the
 * default suite (see the `live-http` group exclusion in `phpunit.xml`).
 */
#[Group('live-http')]
final class UnexpectedThrowableLiveHTTPTest extends ApplicationTestCase
{
    public function test_unexpectedThrowableProducesJSONErrorOverHTTP(): void
    {
        $url = APP_URL.'/api/index.php?'.http_build_query(array(
            APIMethodInterface::REQUEST_PARAM_METHOD => TestUnexpectedThrowableMethod::METHOD_NAME,
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->assertIsString($body, 'The request must have received a response body.');
        $this->assertSame(500, $httpCode, 'An unexpected throwable must produce HTTP 500, never HTTP 200.');
        $this->assertStringContainsString(
            'application/json',
            $contentType,
            'The response must be JSON, never an HTML error page.'
        );

        $data = JSONConverter::json2var($body);

        $this->assertIsArray($data);
        $this->assertSame(APIMethodInterface::ERROR_UNEXPECTED_THROWABLE, $data['code'] ?? null);
        $this->assertArrayHasKey(APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE, $data['data'] ?? array());
        $this->assertNotEmpty($data['data'][APIMethodInterface::RESPONSE_KEY_ERROR_LOG_REFERENCE] ?? null);

        // The throwable's own message must never leak into the client-facing response.
        $this->assertStringNotContainsString(TestUnexpectedThrowableMethod::EXCEPTION_MESSAGE, $body);
    }
}
