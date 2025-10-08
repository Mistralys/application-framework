<?php

declare(strict_types=1);

namespace Application\API\Parameters\ValueLookup;

class SelectableParamValue
{
    public string $value;
    public string $label;

    public function __construct(string $value, string $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
