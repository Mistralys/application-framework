<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for an alias validated according to {@see StringValidations::alias()}.
 *
 * @package API
 * @subpackage Parameters
 */
class AliasParameter extends StringParameter
{
    public function __construct(bool $allowCapitalLetters, string $name, string $label)
    {
        parent::__construct($name, $label);

        $this->validateAs()->alias($allowCapitalLetters);
    }

    public function getAlias() : ?string
    {
        return $this->getValue();
    }
}
