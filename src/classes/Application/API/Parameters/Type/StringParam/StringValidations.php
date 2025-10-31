<?php

declare(strict_types=1);

namespace Application\API\Parameters\Type\StringParam;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\Validation\Type\RegexValidation;
use AppUtils\Microtime;
use AppUtils\OperationResult;
use AppUtils\RegexHelper;
use Throwable;

class StringValidations
{
    private StringParameter $parameter;

    public function __construct(StringParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function alphanumeric() : StringParameter
    {
        return $this->parameter->validateByRegex('/^[a-zA-Z0-9]+$/');
    }

    public function alphabetical() : StringParameter
    {
        return $this->parameter->validateByRegex('/^[a-zA-Z]+$/');
    }

    public function alias(bool $allowCapitalLetters) : StringParameter
    {
        $regex = RegexHelper::REGEX_ALIAS;
        if($allowCapitalLetters) {
            $regex = RegexHelper::REGEX_ALIAS_CAPITALS;
        }

        return $this->parameter->validateBy(new RegexValidation($regex));
    }

    public function label() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_LABEL);
    }

    public function nameOrTitle() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_NAME_OR_TITLE);
    }

    public function md5() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_MD5);
    }

    public function email() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_EMAIL);
    }

    public function url() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_URL);
    }

    public function filename() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_FILENAME);
    }

    public function date() : StringParameter
    {
        return $this->parameter->validateByCallback(function (mixed $value, OperationResult $result) : void
        {
            if(empty($value)) {
                // Let other validations handle empty values (e.g., required)
                return;
            }

            if(!is_string($value)) {
                $result->makeError('Value is not a string.');
                return;
            }

            try{
                Microtime::createFromString($value);
            } catch (Throwable) {
                $result->makeError('Value is not a valid date string.');
                return;
            }
        });
    }
}
