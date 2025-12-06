<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;

class SelectableValueParamStub extends StringParameter implements SelectableValueParamInterface
{
    use SelectableValueParamTrait;

    public const string VALUE_1 = 'value1';
    public const string VALUE_2 = 'value2';
    public const string VALUE_3 = 'value3';

    public function __construct()
    {
        parent::__construct(
            'selectable-value-param-stub',
            'Selectable Value Param Stub'
        );
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }

    protected function _getValues(): array
    {
        return array(
            new SelectableParamValue(self::VALUE_1, 'Value 1'),
            new SelectableParamValue(self::VALUE_2, 'Value 2'),
            new SelectableParamValue(self::VALUE_3, 'Value 3'),
        );
    }
}
