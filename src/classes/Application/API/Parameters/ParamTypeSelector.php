<?php

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\Parameters\CommonTypes\AliasParameter;
use Application\API\Parameters\CommonTypes\AlphabeticalParameter;
use Application\API\Parameters\CommonTypes\AlphanumericParameter;
use Application\API\Parameters\CommonTypes\DateParameter;
use Application\API\Parameters\CommonTypes\EmailParameter;
use Application\API\Parameters\CommonTypes\LabelParameter;
use Application\API\Parameters\CommonTypes\MD5Parameter;
use Application\API\Parameters\CommonTypes\NameOrTitleParameter;
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

    public function alias(bool $allowCapitalLetters) : AliasParameter
    {
        $param = new AliasParameter($allowCapitalLetters, $this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function alphabetical() : AlphabeticalParameter
    {
        $param = new AlphabeticalParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;

    }

    public function alphanumeric() : AlphanumericParameter
    {
        $param = new AlphanumericParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function date() : DateParameter
    {
        $param = new DateParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function email() : EmailParameter
    {
        $param = new EmailParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function label() : LabelParameter
    {
        $param = new LabelParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function md5() : MD5Parameter
    {
        $param = new MD5Parameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }

    public function nameOrTitle() : NameOrTitleParameter
    {
        $param = new NameOrTitleParameter($this->name, $this->label);

        $this->manager->registerParam($param);

        return $param;
    }
}
