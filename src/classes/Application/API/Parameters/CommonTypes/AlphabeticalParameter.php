<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for an alphabetical string validated according to {@see StringValidations::alphabetical()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AlphabeticalParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->alphabetical();
    }

    public function getAlphabetical() : ?string
    {
        return $this->getValue();
    }
}
