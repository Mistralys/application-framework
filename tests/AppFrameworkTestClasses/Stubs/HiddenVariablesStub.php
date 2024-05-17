<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs;

use Application\Interfaces\HiddenVariablesInterface;
use Application\Traits\HiddenVariablesTrait;

class HiddenVariablesStub implements HiddenVariablesInterface
{
    use HiddenVariablesTrait;

    public function addPrivateVar(string $name, $value, ?string $id=null) : self
    {
        return $this->addPrivateHiddenVar($name, $value, $id);
    }
}
