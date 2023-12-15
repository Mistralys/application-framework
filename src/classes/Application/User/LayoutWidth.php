<?php

declare(strict_types=1);

namespace Application\User;

class LayoutWidth
{
    private string $id;
    private string $label;

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getID() : string
    {
        return $this->id;
    }
}
