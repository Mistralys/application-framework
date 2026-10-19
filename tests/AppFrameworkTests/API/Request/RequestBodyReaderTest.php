<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Request;

use Application\API\Request\RequestBodyException;
use Application\API\Request\RequestBodyReader;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see RequestBodyReader}: the byte ceiling, the
 * `post_max_size` discard detection, the read-failure conversion, and
 * {@see RequestBodyReader::fromGlobals()}'s `Content-Length` parsing.
 */
final class RequestBodyReaderTest extends ApplicationTestCase
{
    private string $tempFile = '';

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempFile = sys_get_temp_dir() . '/request-body-reader-test-' . uniqid() . '.tmp';
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        if($this->tempFile !== '' && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    // region: read() ceiling and conversions

    /**
     * A declared length above the ceiling must throw ERROR_BODY_TOO_LARGE
     * without ever opening the stream — the stream URI points at a
     * nonexistent path, so a read attempt would fail with a different code.
     */
    public function test_declaredLengthAboveCeilingThrowsWithoutReading() : void
    {
        $reader = new RequestBodyReader(10, 11, '/nonexistent/path/does-not-exist');

        $this->expectException(RequestBodyException::class);
        $this->expectExceptionCode(RequestBodyException::ERROR_BODY_TOO_LARGE);

        $reader->read();
    }

    /**
     * A stream longer than the ceiling must throw ERROR_BODY_TOO_LARGE,
     * even with no declared length (e.g. a chunked body, or an absent or
     * understated Content-Length).
     */
    public function test_streamLongerThanCeilingThrows() : void
    {
        file_put_contents($this->tempFile, str_repeat('a', 20));

        $reader = new RequestBodyReader(10, null, $this->tempFile);

        $this->expectException(RequestBodyException::class);
        $this->expectExceptionCode(RequestBodyException::ERROR_BODY_TOO_LARGE);

        $reader->read();
    }

    /**
     * An empty stream with a positive declared length must be reported as
     * discarded (PHP's post_max_size behaviour), not as an empty body.
     */
    public function test_emptyStreamWithDeclaredLengthIsDiscarded() : void
    {
        file_put_contents($this->tempFile, '');

        $reader = new RequestBodyReader(10, 5, $this->tempFile);

        $this->expectException(RequestBodyException::class);
        $this->expectExceptionCode(RequestBodyException::ERROR_BODY_DISCARDED);

        $reader->read();
    }

    /**
     * A body within the ceiling must be returned unchanged, untrimmed —
     * including leading/trailing whitespace, since trimming is the
     * caller's responsibility.
     */
    public function test_bodyWithinCeilingIsReturnedUntrimmed() : void
    {
        $body = "  {\"a\":1}  ";
        file_put_contents($this->tempFile, $body);

        $reader = new RequestBodyReader(100, strlen($body), $this->tempFile);

        $this->assertSame($body, $reader->read());
    }

    /**
     * An empty body with no declared length is a legitimate empty request,
     * not a discard — it must be returned as an empty string.
     */
    public function test_emptyBodyWithoutDeclaredLengthIsEmptyString() : void
    {
        file_put_contents($this->tempFile, '');

        $reader = new RequestBodyReader(10, null, $this->tempFile);

        $this->assertSame('', $reader->read());
    }

    /**
     * An unreadable stream (no declared length, nonexistent URI) must
     * throw ERROR_BODY_READ_FAILED, with no PHP warning surfacing.
     */
    public function test_unreadableStreamThrowsReadFailed() : void
    {
        $reader = new RequestBodyReader(10, null, '/nonexistent/path/does-not-exist');

        $error = null;
        set_error_handler(static function(int $errno, string $errstr) use (&$error) : bool {
            // The `@` suppression operator sets error_reporting() to 0 for
            // the duration of the suppressed call, but a custom error
            // handler is still invoked — this mirrors that convention to
            // tell a genuinely suppressed warning apart from one that
            // would otherwise have surfaced.
            if((error_reporting() & $errno) === 0) {
                return true;
            }

            $error = $errstr;
            return true;
        });

        try {
            $this->expectException(RequestBodyException::class);
            $this->expectExceptionCode(RequestBodyException::ERROR_BODY_READ_FAILED);

            $reader->read();
        } finally {
            restore_error_handler();
        }

        $this->assertNull($error, 'No PHP warning must surface from the suppressed read.');
    }

    // endregion

    // region: fromGlobals()

    /**
     * A digit-string CONTENT_LENGTH must be parsed into an int; a missing
     * or non-digit value must become null.
     */
    public function test_fromGlobalsParsesContentLength() : void
    {
        $original = $_SERVER['CONTENT_LENGTH'] ?? null;

        try {
            $_SERVER['CONTENT_LENGTH'] = '1234';
            $this->assertSame(1234, RequestBodyReader::fromGlobals(10000)->getDeclaredLength());

            unset($_SERVER['CONTENT_LENGTH']);
            $this->assertNull(RequestBodyReader::fromGlobals(10000)->getDeclaredLength());

            $_SERVER['CONTENT_LENGTH'] = 'not-a-number';
            $this->assertNull(RequestBodyReader::fromGlobals(10000)->getDeclaredLength());
        } finally {
            if($original === null) {
                unset($_SERVER['CONTENT_LENGTH']);
            } else {
                $_SERVER['CONTENT_LENGTH'] = $original;
            }
        }
    }

    // endregion
}
