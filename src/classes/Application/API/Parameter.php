<?php

declare(strict_types=1);

use AppUtils\Request\RequestParam;

class Application_API_Parameter
{
    protected RequestParam $param;
    protected string $label;
    protected bool $required;
    protected string $description;

    public function __construct(RequestParam $param, string $label, bool $required = false, string $description = '')
    {
        $this->param = $param;
        $this->label = $label;
        $this->required = $required;
        $this->description = $description;
    }

    public function getName() : string
    {
        return $this->param->getName();
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    public function getRequestParam() : RequestParam
    {
        return $this->param;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function hasDescription() : bool
    {
        return !empty($this->description);
    }
}
