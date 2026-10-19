<?php
/**
 * @package API
 * @subpackage Request
 */

declare(strict_types=1);

namespace Application\API\Request;

/**
 * Reads the raw request body under a byte ceiling, converting the three
 * ways a body can fail to fit into distinct exceptions:
 *
 * - The declared (`Content-Length`) length already exceeds the ceiling.
 * - The stream itself is longer than the ceiling (e.g. a chunked body, or
 *   an absent or understated `Content-Length`).
 * - The body reads as an empty string despite a positive declared length —
 *   PHP silently discarded it because it exceeded `post_max_size`.
 *
 * A fourth failure mode, the stream being unreadable at all, is reported
 * as a read failure.
 *
 * This class has no HTTP or superglobal dependency beyond the optional
 * {@see self::fromGlobals()} factory, which is what makes it testable with
 * a temp-file stream URI and an explicit declared length.
 *
 * @package API
 * @subpackage Request
 */
class RequestBodyReader
{
    private int $maxBytes;
    private ?int $declaredLength;
    private string $streamURI;

    /**
     * @param int $maxBytes The byte ceiling. A declared or actual length
     *                      strictly greater than this value is rejected.
     * @param int|null $declaredLength The `Content-Length` value, if known.
     *                                 `null` when the caller has no reliable
     *                                 declared length (e.g. chunked transfer).
     * @param string $streamURI The stream to read from. Defaults to the
     *                          real request body (`php://input`); tests
     *                          pass a temp-file path instead.
     */
    public function __construct(int $maxBytes, ?int $declaredLength = null, string $streamURI = 'php://input')
    {
        $this->maxBytes = $maxBytes;
        $this->declaredLength = $declaredLength;
        $this->streamURI = $streamURI;
    }

    /**
     * Builds a reader for the real, current request, deriving the declared
     * length from `$_SERVER['CONTENT_LENGTH']`.
     *
     * NOTE: `$maxBytes` is used exactly as given — it is not reconciled
     * against the server's `post_max_size`. Callers that want the
     * server-aware limit must resolve it first with
     * {@see self::resolveEffectiveCeiling()} and pass the result here.
     *
     * @param int $maxBytes The byte ceiling.
     * @return self
     */
    public static function fromGlobals(int $maxBytes) : self
    {
        return new self($maxBytes, self::parseContentLength($_SERVER['CONTENT_LENGTH'] ?? null));
    }

    /**
     * Resolves the effective byte ceiling as the lower of the framework's
     * configured ceiling and the server's `post_max_size`.
     *
     * Without this, a client could be told via `maxRequestBodySize` that it
     * may send up to the configured ceiling, while the server actually
     * discards anything over a lower `post_max_size` — a client that
     * shrinks its body to fit the reported limit would still fail.
     *
     * `$postMaxSize` is typically `ini_get('post_max_size')`, which returns
     * `false` when the setting does not exist (practically never, but
     * `ini_get()`'s signature allows it). A value that cannot be confidently
     * parsed as a positive byte quantity (empty, non-numeric, zero,
     * negative, or using a notation `ini_parse_quantity()` does not accept,
     * e.g. `"8 MB"`) is treated as "no server limit known" and the
     * configured ceiling is kept unchanged — the deliberate failure
     * direction is to never report a lower limit than intended, not to
     * mask a genuinely misconfigured server value.
     *
     * @param int $configuredCeiling The framework's own configured ceiling.
     * @param string|false $postMaxSize The raw `post_max_size` ini value.
     * @return int The lower of the two limits, or `$configuredCeiling` when
     *             `$postMaxSize` cannot be parsed as a usable quantity.
     */
    public static function resolveEffectiveCeiling(int $configuredCeiling, string|false $postMaxSize) : int
    {
        if($postMaxSize === false || !preg_match('/^\s*\d+\s*[kmg]?\s*$/i', $postMaxSize)) {
            return $configuredCeiling;
        }

        $serverLimit = ini_parse_quantity($postMaxSize);

        if($serverLimit <= 0) {
            return $configuredCeiling;
        }

        return min($configuredCeiling, $serverLimit);
    }

    /**
     * Parses a raw `Content-Length` header value. Only a value consisting
     * entirely of digits is accepted; anything else (missing, empty,
     * non-numeric, or carrying a sign) is treated as unknown.
     *
     * @param mixed $value
     * @return int|null
     */
    private static function parseContentLength(mixed $value) : ?int
    {
        if(!is_string($value) && !is_int($value)) {
            return null;
        }

        $value = (string)$value;

        if($value === '' || !ctype_digit($value)) {
            return null;
        }

        return (int)$value;
    }

    /**
     * Reads the body, enforcing the byte ceiling and detecting a
     * `post_max_size` discard and an unreadable stream.
     *
     * At most {@see self::getMaxBytes()} + 1 bytes are ever buffered: the
     * extra byte is what lets an over-ceiling stream be detected without
     * reading the whole of a potentially much larger payload.
     *
     * @return string The raw body, exactly as read — untrimmed.
     * @throws RequestBodyException {@see RequestBodyException::ERROR_BODY_TOO_LARGE}
     *         {@see RequestBodyException::ERROR_BODY_DISCARDED}
     *         {@see RequestBodyException::ERROR_BODY_READ_FAILED}
     */
    public function read() : string
    {
        if($this->declaredLength !== null && $this->declaredLength > $this->maxBytes) {
            throw new RequestBodyException(
                'Declared request body length exceeds the configured ceiling.',
                sprintf(
                    'The declared Content-Length [%d] exceeds the configured maximum of [%d] bytes. The stream was never opened.',
                    $this->declaredLength,
                    $this->maxBytes
                ),
                RequestBodyException::ERROR_BODY_TOO_LARGE
            );
        }

        // The PHP warning on a failed or truncated read is suppressed:
        // the failure surfaces exclusively through the exceptions below.
        $raw = @file_get_contents($this->streamURI, false, null, 0, $this->maxBytes + 1);

        if($raw === false) {
            throw new RequestBodyException(
                'Failed to read the request body.',
                sprintf('file_get_contents() on stream [%s] returned false.', $this->streamURI),
                RequestBodyException::ERROR_BODY_READ_FAILED
            );
        }

        if(strlen($raw) > $this->maxBytes) {
            throw new RequestBodyException(
                'Request body exceeds the configured ceiling.',
                sprintf(
                    'The stream [%s] yielded more than [%d] bytes, the configured maximum.',
                    $this->streamURI,
                    $this->maxBytes
                ),
                RequestBodyException::ERROR_BODY_TOO_LARGE
            );
        }

        if($raw === '' && $this->declaredLength !== null && $this->declaredLength > 0) {
            throw new RequestBodyException(
                'Request body was discarded by the server.',
                sprintf(
                    'The stream [%s] read as an empty string despite a declared Content-Length of [%d], indicating the body was discarded by the server (post_max_size).',
                    $this->streamURI,
                    $this->declaredLength
                ),
                RequestBodyException::ERROR_BODY_DISCARDED
            );
        }

        return $raw;
    }

    public function getMaxBytes() : int
    {
        return $this->maxBytes;
    }

    public function getDeclaredLength() : ?int
    {
        return $this->declaredLength;
    }
}
