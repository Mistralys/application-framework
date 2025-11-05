<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for a label validated according to {@see StringValidations::label()}.
 *
 * @package API
 * @subpackage Parameters
 */
class LabelParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->label();
    }

    public function getLabelValue() : ?string
    {
        return $this->getValue();
    }
}
