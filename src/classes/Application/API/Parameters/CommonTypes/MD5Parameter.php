<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\CommonTypes;

use Application\API\Parameters\Type\StringParameter;

/**
 * Parameter for an MD5 hash.
 *
 * @package API
 * @subpackage Parameters
 */
class MD5Parameter extends StringParameter
{
    protected function _init(): void
    {
        $this->validateAs()->md5();
    }

    public function getMD5() : ?string
    {
        return $this->getValue();
    }
}
