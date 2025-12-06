<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for an alphanumeric string validated according to {@see StringValidations::alphanumeric()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AlphanumericParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->alphanumeric();
    }

    public function getAlphanumeric() : ?string
    {
        return $this->getValue();
    }
}
