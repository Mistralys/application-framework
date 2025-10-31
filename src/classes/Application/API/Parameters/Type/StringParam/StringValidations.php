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
    public const string REGEX_ALPHA = '/^[a-zA-Z]+$/';
    public const string REGEX_ALNUM = '/^[a-zA-Z0-9]+$/';

    private StringParameter $parameter;

    public function __construct(StringParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return StringParameter
     * @see self::REGEX_ALNUM
     */
    public function alphanumeric() : StringParameter
    {
        return $this->parameter->validateByRegex(self::REGEX_ALNUM);
    }

    /**
     * @return StringParameter
     * @see self::REGEX_ALPHA
     */
    public function alphabetical() : StringParameter
    {
        return $this->parameter->validateByRegex(self::REGEX_ALPHA);
    }

    /**
     * @param bool $allowCapitalLetters Whether to allow capital letters in the alias.
     * @return StringParameter
     * @see RegexHelper::REGEX_ALIAS
     * @see RegexHelper::REGEX_ALIAS_CAPITALS
     */
    public function alias(bool $allowCapitalLetters) : StringParameter
    {
        $regex = RegexHelper::REGEX_ALIAS;
        if($allowCapitalLetters) {
            $regex = RegexHelper::REGEX_ALIAS_CAPITALS;
        }

        return $this->parameter->validateBy(new RegexValidation($regex));
    }

    /**
     * @return StringParameter
     * @see RegexHelper::REGEX_LABEL
     */
    public function label() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_LABEL);
    }

    /**
     * @return StringParameter
     * @see RegexHelper::REGEX_NAME_OR_TITLE
     */
    public function nameOrTitle() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_NAME_OR_TITLE);
    }

    public function md5() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_MD5);
    }

    /**
     * @return StringParameter
     * @see RegexHelper::REGEX_EMAIL
     */
    public function email() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_EMAIL);
    }

    /**
     * @return StringParameter
     * @see RegexHelper::REGEX_URL
     */
    public function url() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_URL);
    }

    /**
     * @return StringParameter
     * @see RegexHelper::REGEX_FILENAME
     */
    public function filename() : StringParameter
    {
        return $this->parameter->validateByRegex(RegexHelper::REGEX_FILENAME);
    }

    /**
     * @return StringParameter
     * @see Microtime::createFromString()
     */
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
