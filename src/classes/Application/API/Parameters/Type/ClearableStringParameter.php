<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\Validation\ParamValidationInterface;

/**
 * Clearable string API parameter with three-state resolution semantics.
 *
 * Unlike {@see StringParameter}, this type distinguishes between an absent
 * parameter and a present-but-empty parameter, enabling Update-style API
 * methods to explicitly clear optional metadata fields:
 *
 * - **Absent** (key not in `$_REQUEST`) → `null`
 * - **Present but empty** (empty string or whitespace-only after trim) → `''`
 * - **Present with value** (non-empty after trim) → trimmed string
 *
 * Resolving through {@see BaseAPIParameter::hasRawRequestValue()} /
 * {@see BaseAPIParameter::getRawRequestValueUnprocessed()} rather than
 * `RequestParam::get()` is intentional: the framework's `RequestParam::get()`
 * discards empty strings before the parameter type ever sees them, which
 * would collapse the absent/empty distinction this type relies on. Resolving
 * through the value-source seam (rather than reading `$_REQUEST` directly) is
 * what lets this type's absent/empty/value distinction also work for a
 * JSON-body method's body-delivered parameters, not just the query string.
 *
 * @package API
 * @subpackage Parameters
 */
class ClearableStringParameter extends StringParameter
{
    public function getTypeLabel(): string
    {
        return t('Clearable string');
    }

    /**
     * Skips format validation when the resolved value is the empty-string
     * clear signal. A `null` value (absent parameter) already skips
     * individual validators via their own null guards.
     *
     * @param int|float|bool|string|array<int|string,mixed>|null $value
     * @return bool
     */
    protected function isValueValidatable(int|float|bool|string|array|null $value) : bool
    {
        if($value === '') {
            return false;
        }

        return parent::isValueValidatable($value);
    }

    /**
     * Resolves the parameter value from its backing store (the request, or
     * a JSON body for a {@see \Application\API\Traits\JSONRequestInterface}
     * method) with three-state semantics.
     *
     * - Key absent → `null`
     * - Key present, value empty or whitespace-only (after trim) → `''`
     * - Key present, value non-empty (after trim) → trimmed string
     * - Numeric values → string representation of the number
     * - Non-string, non-numeric values → `null` (with a warning)
     *
     * @return string|null
     */
    protected function resolveValue(): ?string
    {
        if(!$this->hasRawRequestValue()) {
            return null;
        }

        $raw = $this->getRawRequestValueUnprocessed();

        if(is_numeric($raw)) {
            return trim((string)$raw);
        }

        if(!is_string($raw)) {
            $this->result->makeWarning(
                sprintf('The value must be a string, [%s] given.', gettype($raw)),
                ParamValidationInterface::VALIDATION_INVALID_VALUE_TYPE
            );
            return null;
        }

        $trimmed = trim($raw);

        if($trimmed === '') {
            return '';
        }

        return $trimmed;
    }
}
