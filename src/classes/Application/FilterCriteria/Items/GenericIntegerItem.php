<?php

declare(strict_types=1);

namespace Application\FilterCriteria\Items;

use Application\FilterCriteria\Items\BaseIntegerItem;

class GenericIntegerItem extends BaseIntegerItem
{
    private int $id;
    private string $label;

    public function __construct(int $id, string $label, array $data = array())
    {
        $this->id = $id;
        $this->label = $label;

        parent::__construct($data);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getID(): int
    {
        return $this->id;
    }
}
