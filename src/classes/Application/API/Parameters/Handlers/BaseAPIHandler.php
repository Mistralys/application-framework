<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParamManager;

abstract class BaseAPIHandler implements APIHandlerInterface
{
    protected mixed $selectedValue = null;
    protected APIParamManager $manager;

    public function __construct(APIParamManager|APIMethodInterface $manager)
    {
        if($manager instanceof APIMethodInterface) {
            $manager = $manager->manageParams();
        }

        $this->manager = $manager;
    }

    public function selectValue(mixed $value) : self
    {
        $this->selectedValue = $value;
        return $this;
    }

    public function resolveValue() : mixed
    {
        return $this->selectedValue ?? $this->resolveValueFromSubject();
    }

    public function requireValue(): string|int|float|bool|array|object
    {
        $value = $this->resolveValue();

        if($value === null) {
            throw new APIParameterException(
                'Required parameter value is missing.'
            );
        }

        return $value;
    }

    /**
     * This is called when no value has been selected directly.
     * The value must be resolved from the parameter itself.
     *
     * @return mixed
     */
    abstract protected function resolveValueFromSubject() : mixed;
}
