<?php

class Application_API_Parameter
{
    /**
     * @var AppUtils\Request_Param
     */
    protected $param;

    protected $label;

    protected $required;

    protected $description;

    public function __construct(AppUtils\Request_Param $param, $label, $required = false, $description = null)
    {
        $this->param = $param;
        $this->label = $label;
        $this->required = $required;
        $this->description = $description;
    }

    public function getName()
    {
        return $this->param->getName();
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getRequestParam()
    {
        return $this->param;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function hasDescription()
    {
        return !empty($this->description);
    }
}