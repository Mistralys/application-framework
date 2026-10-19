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

    // region: Exact boundaries

    /**
     * A declared length exactly equal to the ceiling must be accepted —
     * the boundary check in {@see RequestBodyReader::read()} is strictly
     * greater-than, not greater-than-or-equal.
     */
    public function test_declaredLengthEqualToCeilingIsAccepted() : void
    {
        $body = str_repeat('a', 10);
        file_put_contents($this->tempFile, $body);

        $reader = new RequestBodyReader(10, 10, $this->tempFile);

        $this->assertSame($body, $reader->read());
    }

    /**
     * A stream whose actual length is exactly equal to the ceiling must be
     * accepted, with no declared length to short-circuit the check.
     */
    public function test_streamLengthEqualToCeilingIsAccepted() : void
    {
        $body = str_repeat('a', 10);
        file_put_contents($this->tempFile, $body);

        $reader = new RequestBodyReader(10, null, $this->tempFile);

        $this->assertSame($body, $reader->read());
    }

    /**
     * A zero-byte ceiling against a genuinely empty stream must be
     * accepted, returning an empty string rather than throwing.
     */
    public function test_zeroCeilingAcceptsEmptyBody() : void
    {
        file_put_contents($this->tempFile, '');

        $reader = new RequestBodyReader(0, null, $this->tempFile);

        $this->assertSame('', $reader->read());
    }

    /**
     * A zero-byte ceiling against a single-byte stream must throw
     * ERROR_BODY_TOO_LARGE, confirming the ceiling check also fires at the
     * smallest possible non-zero overage.
     */
    public function test_zeroCeilingRejectsOneByte() : void
    {
        file_put_contents($this->tempFile, 'a');

        $reader = new RequestBodyReader(0, null, $this->tempFile);

        $this->expectException(RequestBodyException::class);
        $this->expectExceptionCode(RequestBodyException::ERROR_BODY_TOO_LARGE);

        $reader->read();
    }

    /**
     * An empty body with a declared length of exactly zero must be
     * accepted as a legitimate empty request, not treated as a
     * post_max_size discard — the discard check requires the declared
     * length to be strictly greater than zero.
     */
    public function test_zeroDeclaredLengthWithEmptyBodyIsNotDiscarded() : void
    {
        file_put_contents($this->tempFile, '');

        $reader = new RequestBodyReader(10, 0, $this->tempFile);

        $this->assertSame('', $reader->read());
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

    /**
     * {@see RequestBodyReader::fromGlobals()} must use the ceiling exactly
     * as given, with no implicit reconciliation against `post_max_size` —
     * that reconciliation is the caller's responsibility, performed via
     * {@see RequestBodyReader::resolveEffectiveCeiling()} before the value
     * ever reaches `fromGlobals()`.
     */
    public function test_fromGlobalsUsesCeilingAsGiven() : void
    {
        $this->assertSame(12345, RequestBodyReader::fromGlobals(12345)->getMaxBytes());
    }

    // endregion

    // region: resolveEffectiveCeiling()

    public function test_resolveEffectiveCeilingTakesLowerServerLimit() : void
    {
        $configured = 15029589;

        $this->assertSame(8388608, RequestBodyReader::resolveEffectiveCeiling($configured, '8M'));
        $this->assertSame(524288, RequestBodyReader::resolveEffectiveCeiling($configured, '512K'));
        $this->assertSame(1048576, RequestBodyReader::resolveEffectiveCeiling($configured, '1048576'));
    }

    public function test_resolveEffectiveCeilingKeepsConfiguredWhenServerLimitIsHigher() : void
    {
        $configured = 15029589;

        $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, '1G'));
    }

    /**
     * Every value that cannot be confidently parsed as a positive byte
     * quantity must leave the configured ceiling unchanged, and must never
     * surface a PHP warning — the format guard must reject these before
     * `ini_parse_quantity()` ever sees them.
     */
    public function test_resolveEffectiveCeilingIgnoresUnusableValues() : void
    {
        $configured = 15029589;

        $error = null;
        set_error_handler(static function(int $errno, string $errstr) use (&$error) : bool {
            if((error_reporting() & $errno) === 0) {
                return true;
            }

            $error = $errstr;
            return true;
        });

        try {
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, false));
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, ''));
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, '0'));
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, 'abc'));
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, '-1'));
            $this->assertSame($configured, RequestBodyReader::resolveEffectiveCeiling($configured, '8 MB'));
        } finally {
            restore_error_handler();
        }

        $this->assertNull($error, 'No PHP warning must surface from an unusable post_max_size value.');
    }

    // endregion
}
