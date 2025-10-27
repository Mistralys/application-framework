<?php

declare(strict_types=1);

namespace Application\API\Groups;

class GenericAPIGroup implements APIGroupInterface
{
    protected string $id;
    protected string $label;
    protected string $description;

    public function __construct(string $id, string $label, string $description)
    {
        $this->id = $id;
        $this->label = $label;
        $this->description = $description;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
}
