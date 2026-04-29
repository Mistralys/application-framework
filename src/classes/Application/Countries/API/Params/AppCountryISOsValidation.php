<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API\Params;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Validation\BaseParamValidation;
use Application\AppFactory;
use AppUtils\OperationResult;

/**
 * Validates each country ISO code in a string list individually, producing
 * a per-ISO error message that identifies which ISO codes do not exist.
 *
 * This is necessary because {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation}
 * passes the entire resolved value (the `string[]` array) to the callback as a
 * single argument — it does not iterate per item. Using a custom validation
 * class gives consumers precise feedback on which ISO codes are invalid.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryISOsParam
 */
class AppCountryISOsValidation extends BaseParamValidation
{
    public const int VALIDATION_COUNTRY_ISO_NOT_EXISTS = 184802;

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if(!is_array($value) || empty($value)) {
            return;
        }

        $countries = AppFactory::createCountries();
        $invalid = array();

        foreach($value as $iso) {
            if(!$countries->isoExists((string)$iso)) {
                $invalid[] = $iso;
            }
        }

        if(!empty($invalid)) {
            $result->makeError(
                sprintf(
                    'The following country ISO codes do not exist for parameter `%s`: %s',
                    $param->getName(),
                    implode(', ', $invalid)
                ),
                self::VALIDATION_COUNTRY_ISO_NOT_EXISTS
            );
        }
    }
}
