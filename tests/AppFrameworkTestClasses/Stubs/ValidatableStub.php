<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application_Interfaces_Validatable;
use Application_Traits_Validatable;

class ValidatableStub implements Application_Interfaces_Validatable
{
    use Application_Traits_Validatable;

    public const ERROR_CODE = 42;
    public const MESSAGE = 'Error Message';

    private array $messages = array(
        self::ERROR_CODE => self::MESSAGE,
    );

    private ?int $targetCode;

    public function __construct(?int $code=null)
    {
        $this->targetCode = $code;
    }

    protected function _isValid(): bool
    {
        if($this->targetCode !== null) {
            $this->setValidationError($this->messages[$this->targetCode], $this->targetCode);
            return false;
        }

        return true;
    }
}
