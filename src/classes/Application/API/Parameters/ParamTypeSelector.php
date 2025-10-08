<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\Parameters\Type\BooleanParameter;
use Application\API\Parameters\Type\IDListParameter;
use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\Type\JSONParameter;
use Application\API\Parameters\Type\StringParameter;

class ParamTypeSelector
{
    private APIParamManager $manager;
    private string $name;
    private string $label;

    public function __construct(APIParamManager $manager, string $name, string $label)
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->label = $label;
    }

    public function boolean() : BooleanParameter
    {
        $param = new BooleanParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function integer() : IntegerParameter
    {
        $param = new IntegerParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function string() : StringParameter
    {
        $param = new StringParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    /**
     * List of integer IDs as an array.
     *
     * @return IDListParameter
     * @throws APIParameterException
     */
    public function idList() : IDListParameter
    {
        $param = new IDListParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function JSON() : JSONParameter
    {
        $param = new JSONParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;

    }
}
