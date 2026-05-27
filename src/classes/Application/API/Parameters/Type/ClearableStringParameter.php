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
 * Reading `$_REQUEST` directly via `array_key_exists()` is intentional:
 * the framework's `RequestParam::get()` discards empty strings before the
 * parameter type ever sees them, which would collapse the absent/empty
 * distinction that this type relies on.
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
     * Resolves the parameter value from `$_REQUEST` with three-state semantics.
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
        if(!array_key_exists($this->getName(), $_REQUEST)) {
            return null;
        }

        $raw = $_REQUEST[$this->getName()];

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
