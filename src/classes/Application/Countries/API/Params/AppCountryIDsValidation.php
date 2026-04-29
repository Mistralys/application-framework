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
 * Validates each country ID in an ID list individually, producing
 * a per-ID error message that identifies which IDs do not exist.
 *
 * This is necessary because {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation}
 * passes the entire resolved value (the `int[]` array) to the callback as a
 * single argument — it does not iterate per item. Using a custom validation
 * class gives consumers precise feedback on which IDs are invalid.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryIDsParam
 */
class AppCountryIDsValidation extends BaseParamValidation
{
    public const int VALIDATION_COUNTRY_ID_NOT_EXISTS = 184801;

    public function validate(float|int|bool|array|string|null $value, OperationResult $result, APIParameterInterface $param): void
    {
        if(!is_array($value) || empty($value)) {
            return;
        }

        $countries = AppFactory::createCountries();
        $invalid = array();

        foreach($value as $id) {
            if(!$countries->idExists((int)$id)) {
                $invalid[] = $id;
            }
        }

        if(!empty($invalid)) {
            $result->makeError(
                sprintf(
                    'The following country IDs do not exist for parameter `%s`: %s',
                    $param->getName(),
                    implode(', ', $invalid)
                ),
                self::VALIDATION_COUNTRY_ID_NOT_EXISTS
            );
        }
    }
}
