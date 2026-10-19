<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\Application;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;

/**
 * @package API
 * @subpackage Traits
 * @see JSONRequestInterface
 */
trait JSONRequestTrait
{
    /**
     * Maximum number of characters an echoed string value may have in a
     * development-mode error response before it is replaced with
     * `[N characters truncated]` — see {@see self::collectRequestErrorData()}.
     */
    private const int ECHO_MAX_STRING_LENGTH = 512;

    private ?ArrayDataCollection $requestData = null;

    /**
     * The echo form of the raw request body, recorded once by
     * {@see self::getRequestData()} when the body could not be parsed
     * (invalid JSON, or a top level that is not an object) — the raw body
     * verbatim when at most {@see self::ECHO_MAX_STRING_LENGTH} characters,
     * otherwise `[N characters truncated]`. `null` until a parse has failed.
     */
    private ?string $unparseableBody = null;

    protected function collectRequestData(string $version) : void
    {
        // Historically, this is the point the base method pipeline collects
        // request data at. Parameters resolving from a JSONBodyValueSource
        // (see Application\API\Parameters\ValueSource\JSONBodyValueSource)
        // typically trigger the lazy parse earlier, during validate() — in
        // which case this call is a no-op, since getRequestData() parses
        // only once per request.
        $this->getRequestData();
    }

    /**
     * Lazily parses and returns the JSON request body, once per request.
     *
     * Re-entry guard (audit cycle 3, Major 1): {@see \Application\API\BaseMethods\BaseAPIMethod::errorResponse()}
     * calls {@see self::collectRequestErrorData()} while building every
     * error response, which in a development environment reads this very
     * method. Without a guard, an unparseable body would recurse without
     * end: the first call assigns a provisional empty collection to
     * `$requestData` *before* reading or parsing the body, so a reentrant
     * call triggered while building the resulting error response sees that
     * already non-null collection and returns it immediately, instead of
     * reading or parsing the body a second time.
     *
     * @return ArrayDataCollection
     */
    public function getRequestData(): ArrayDataCollection
    {
        if($this->requestData !== null) {
            return $this->requestData;
        }

        // Provisional empty collection, assigned before the body is read —
        // see the re-entry guard note above.
        $this->requestData = ArrayDataCollection::create();

        $body = $this->getRequestBody();

        try {
            // An empty (or whitespace-only) body is treated as an empty
            // object, so query-only callers keep working unchanged.
            $decoded = $body === '' ? array() : JSONConverter::json2array($body);
        } catch (JSONConverterException) {
            $this->rejectUnparseableBody($body);
        }

        if(!$this->isJSONObjectShaped($decoded)) {
            $this->rejectUnparseableBody($body);
        }

        $this->requestData = ArrayDataCollection::create($decoded);

        return $this->requestData;
    }

    /**
     * Whether the decoded JSON top level is object-shaped: an empty array
     * (ambiguous between `{}` and `[]`, treated as an object) or an
     * associative array. A non-empty sequential list (e.g. `[1,2]`) is not.
     *
     * @param array<int|string,mixed> $decoded
     * @return bool
     */
    private function isJSONObjectShaped(array $decoded) : bool
    {
        return $decoded === array() || !array_is_list($decoded);
    }

    /**
     * Records the echo form of the raw body and sends the standard
     * `ERROR_FAILED_TO_READ_INPUT` (182801) response. Reads only the raw
     * body string handed to it — never the parser — so it cannot itself
     * re-enter the parse.
     *
     * @param string $body
     * @return never
     */
    private function rejectUnparseableBody(string $body) : never
    {
        $this->unparseableBody = $this->truncateEcho($body);

        $this->errorResponse(self::ERROR_FAILED_TO_READ_INPUT)
            ->setErrorMessage('Failed to read JSON input data.')
            ->send();
    }

    public function getRequestMime() : string
    {
        return 'application/json';
    }

    /**
     * SECURITY: This echoes the parsed request body verbatim, which is
     * genuinely useful for local debugging but must never reach a client
     * outside a development environment — a request body can legitimately
     * contain sensitive domain data. Gated on {@see Application::isDevelEnvironment()}
     * here at the trait level (an empty array short-circuits the contribution
     * entirely), and independently gated again by {@see \Application\API\ErrorResponse::getErrorData()}
     * on the same check, since this method's return value is routed through
     * {@see \Application\API\ErrorResponse::addRequestData()} — the
     * dedicated, redaction-subject channel — not {@see \Application\API\ErrorResponse::addData()}.
     * An API key never appears here regardless: it is a header-only
     * parameter (see the contract comment on
     * {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()})
     * and is never part of the request body this trait parses.
     *
     * Every string value echoed here, at any nesting depth, is truncated to
     * {@see self::ECHO_MAX_STRING_LENGTH} characters — covering registered
     * and unregistered keys alike (e.g. a mistyped `fileContent`), with no
     * parameter lookup involved.
     *
     * @return array<string,mixed>
     */
    protected function collectRequestErrorData() : array
    {
        if(!Application::isDevelEnvironment()) {
            return array();
        }

        if($this->unparseableBody !== null) {
            return array(
                JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA => array(
                    JSONRequestInterface::RESPONSE_KEY_ERROR_UNPARSEABLE_BODY => $this->unparseableBody
                )
            );
        }

        return array(
            // Serialized explicitly via getData(): ArrayDataCollection is not
            // JsonSerializable, so passing the object itself would currently
            // render as an empty '{}' in the JSON response — accidentally
            // safe, but not something this redaction boundary should rely on.
            JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA => $this->truncateLongStrings($this->getRequestData()->getData())
        );
    }

    /**
     * Recursively replaces every string value longer than
     * {@see self::ECHO_MAX_STRING_LENGTH} characters with
     * `[N characters truncated]`, at any nesting depth.
     *
     * @param mixed $value
     * @return mixed
     */
    private function truncateLongStrings(mixed $value) : mixed
    {
        if(is_array($value)) {
            foreach($value as $key => $item) {
                $value[$key] = $this->truncateLongStrings($item);
            }

            return $value;
        }

        if(is_string($value)) {
            return $this->truncateEcho($value);
        }

        return $value;
    }

    private function truncateEcho(string $value) : string
    {
        $length = mb_strlen($value);

        if($length <= self::ECHO_MAX_STRING_LENGTH) {
            return $value;
        }

        return sprintf('[%d characters truncated]', $length);
    }
}
