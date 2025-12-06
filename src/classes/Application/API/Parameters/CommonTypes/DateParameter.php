<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;
use AppUtils\Microtime;

/**
 * Parameter for a date string, with or without time.
 *
 * @package API
 * @subpackage Parameters
 */
class DateParameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->date();
    }

    public function getDate() : ?Microtime
    {
        $value = $this->getValue();
        if(!empty($value)) {
            return new Microtime($value);
        }

        return null;
    }
}
