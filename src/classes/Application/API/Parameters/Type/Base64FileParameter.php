<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\ConfigSettings\AppConfig;

/**
 * Base64 File API Parameter.
 *
 * Accepts file content as a base64-encoded string, either plain or with an
 * optional `data:<mime>;base64,` URI prefix, and exposes the decoded binary
 * content.
 *
 * - An absent or empty value resolves to `null`.
 * - A non-string value is a {@see ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE} warning.
 * - All whitespace and an optional `data:<mime>;base64,` prefix are stripped before decoding.
 * - The decoded size is computed from the encoded length and checked against
 *   the configured limit **before** decoding; an oversized value is rejected
 *   with {@see ParamValidationInterface::VALIDATION_FILE_SIZE_EXCEEDED} without
 *   ever being decoded.
 * - A value that fails strict base64 decoding is rejected with
 *   {@see ParamValidationInterface::VALIDATION_INVALID_BASE64}.
 *
 * The parameter retains exactly one copy of the value: the decoded binary
 * content. The normalised base64 string used for the size check and decoding
 * is a local variable in {@see self::resolveValue()} that goes out of scope
 * once resolution completes, and is never decoded at all when the size check
 * fails.
 *
 * @package API
 * @subpackage Parameters
 */
class Base64FileParameter extends BaseAPIParameter
{
    private ?int $maxSize = null;

    public function getTypeLabel(): string
    {
        return t('Base64 File');
    }

    public function getDefaultValue(): ?string
    {
        return is_string($this->defaultValue) ? $this->defaultValue : null;
    }

    /**
     * Overrides the maximum accepted decoded content size, in bytes.
     * The default is {@see AppConfig::getAPIMaxFileContentSize()}.
     *
     * @param int $bytes
     * @return $this
     */
    public function setMaxSize(int $bytes) : self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    /**
     * The maximum accepted decoded content size, in bytes: the value set
     * via {@see self::setMaxSize()}, or {@see AppConfig::getAPIMaxFileContentSize()}
     * otherwise.
     *
     * @return int
     */
    public function getMaxSize() : int
    {
        if($this->maxSize !== null) {
            return $this->maxSize;
        }

        return AppConfig::getAPIMaxFileContentSize();
    }

    protected function resolveValue(): ?string
    {
        $value = $this->getRawRequestValue();

        if($value === null || $value === '') {
            return null;
        }

        if(!is_string($value)) {
            $this->result->makeWarning(
                sprintf(
                    'The value must be a base64-encoded string, [%s] given.',
                    gettype($value)
                ),
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );

            return null;
        }

        $normalized = self::normalize($value);

        if($normalized === '') {
            return null;
        }

        $maxSize = $this->getMaxSize();
        $decodedSize = self::calculateDecodedSize($normalized);

        if($decodedSize > $maxSize) {
            $this->result->makeError(
                sprintf(
                    'The decoded file content size (%d bytes) exceeds the maximum allowed size of %d bytes.',
                    $decodedSize,
                    $maxSize
                ),
                ParamValidationInterface::VALIDATION_FILE_SIZE_EXCEEDED
            );

            return null;
        }

        $decoded = base64_decode($normalized, true);

        if($decoded === false) {
            $this->result->makeError(
                'The value is not a valid base64-encoded string.',
                ParamValidationInterface::VALIDATION_INVALID_BASE64
            );

            return null;
        }

        return $decoded;
    }

    /**
     * Strips an optional `data:<mime>;base64,` URI prefix and all whitespace
     * from the raw value, leaving a normalised base64 string ready for the
     * size check and decoding.
     *
     * @param string $value
     * @return string
     */
    private static function normalize(string $value) : string
    {
        if(preg_match('/^data:[^,]*;base64,(.*)$/s', $value, $matches) === 1) {
            $value = $matches[1];
        }

        return (string)preg_replace('/\s+/', '', $value);
    }

    /**
     * Computes the exact decoded byte size of a normalised base64 string
     * from its encoded length, without decoding it. Exact for well-formed
     * input, padded or not: `intdiv(strlen($normalised) * 3, 4) - $padding`,
     * where `$padding` is the count of trailing `=` characters (0, 1 or 2).
     *
     * @param string $normalized
     * @return int
     */
    private static function calculateDecodedSize(string $normalized) : int
    {
        $padding = 0;

        if(str_ends_with($normalized, '==')) {
            $padding = 2;
        } else if(str_ends_with($normalized, '=')) {
            $padding = 1;
        }

        return intdiv(strlen($normalized) * 3, 4) - $padding;
    }

    public function getValue() : ?string
    {
        $value = parent::getValue();
        if(is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Typed alias of {@see self::getValue()}: the decoded binary content.
     * @return string|NULL
     */
    public function getDecodedContent() : ?string
    {
        return $this->getValue();
    }

    /**
     * The byte size of the decoded content, or `0` when the parameter
     * has no value.
     *
     * @return int
     */
    public function getDecodedSize() : int
    {
        $value = $this->getValue();

        if($value === null) {
            return 0;
        }

        return strlen($value);
    }
}
