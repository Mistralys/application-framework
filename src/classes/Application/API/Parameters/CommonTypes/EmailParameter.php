<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParam\StringValidations;
use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for an email address according to {@see StringValidations::email()}.
 *
 * @package API
 * @subpackage Parameters
 */
class EmailParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->email();
    }

    public function getEmail() : ?string
    {
        return $this->getValue();
    }
}
