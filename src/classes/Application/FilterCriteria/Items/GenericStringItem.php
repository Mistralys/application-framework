<?php

declare(strict_types=1);

namespace Application\FilterCriteria\Items;

class GenericStringItem extends BaseStringItem
{
    private string $id;
    private string $label;

    public function __construct(string $id, string $label, array $data = array())
    {
        $this->id = $id;
        $this->label = $label;

        parent::__construct($data);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getID(): string
    {
        return $this->id;
    }
}
