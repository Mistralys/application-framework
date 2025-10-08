<?php

declare(strict_types=1);

namespace Application\API\Parameters\Validation;

use Application\Validation\ValidationResults;

class ParamValidationResults extends ValidationResults
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
