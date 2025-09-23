<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

use AppUtils\OperationResult_Collection;

class ParamValidationResults extends OperationResult_Collection
{
    public function serializeErrors() : array
    {
        $result = array();

        foreach($this->getErrors() as $error) {
            $result[] = array(
                'message' => $error->getErrorMessage(),
                'code' => $error->getCode()
            );
        }

        return $result;
    }
}
