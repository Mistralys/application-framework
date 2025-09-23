<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Type;

use Application\API\Parameters\BaseAPIParameter;
use AppUtils\ConvertHelper;

/**
 * API Parameter: List of integer IDs as an array.
 *
 * @package API
 * @subpackage Parameters
 */
class IDListParameter extends BaseAPIParameter
{
    /**
     * @var int[]
     */
    private array $defaultValue = array();

    /**
     * @return int[]
     */
    public function getDefaultValue(): array
    {
        return $this->defaultValue;
    }

    /**
     * @param array<int|string,int|float|string> $default
     * @return $this
     */
    public function setDefaultValue(array $default) : self
    {
        $this->defaultValue = array();

        foreach($default as $id)
        {
            if(!is_numeric($id)) {
                continue;
            }

            $this->defaultValue[] = (int)$id;
        }

        return $this;
    }

    /**
     * @return int[]|null
     */
    protected function resolveValue(): array|null
    {
        $value = $this->getRequestParam()->get();
        if(!is_array($value) && !is_string($value)) {
            return null;
        }

        if(is_string($value)) {
            $value = ConvertHelper::explodeTrim(',', $value);
        }

        $result = array();
        foreach($value as $id)
        {
            if(!is_numeric($id)) {
                continue;
            }

            $result[] = (int)$id;
        }

        return $result;
    }
}
