<?php

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParam\StringValidations;
use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for a name or title validated according to {@see StringValidations::nameOrTitle()}.
 *
 * @package API
 * @subpackage Parameters
 */
class NameOrTitleParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->nameOrTitle();
    }

    public function getNameOrTitle() : ?string
    {
        return $this->getValue();
    }
}
