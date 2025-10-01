<?php

declare(strict_types=1);

namespace Application\API\Parameters\Reserved;

use Application\API\APIMethodInterface;
use Application\API\Parameters\ReservedParamInterface;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamTrait;

class APIVersionParameter extends StringParameter implements ReservedParamInterface, SelectableValueParamInterface
{
    use SelectableValueParamTrait;

    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;

        parent::__construct(APIMethodInterface::REQUEST_PARAM_API_VERSION, 'API Version');

        $this
            ->setDescription(
                'The version of the API to use for this request. If not provided, the current version (v%1$s) will be used. Supported versions are: %2$s',
                $method->getCurrentVersion(),
                implode(', ', $method->getVersions())
            )
            ->validateByEnum($method->getVersions());
    }

    public function isEditable(): bool
    {
        return true;
    }

    protected function _getValues(): array
    {
        $result = array();

        foreach($this->method->getVersions() as $version) {
            $result[] = new SelectableParamValue($version, 'v'.$version);
        }

        return $result;
    }

    public function getDefaultSelectableValue(): ?SelectableParamValue
    {
        return null;
    }
}
