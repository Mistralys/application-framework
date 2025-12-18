<?php

declare(strict_types=1);

namespace NewsCentral;

use AppUtils\Interfaces\StringPrimaryRecordInterface;
use UI_Icon;

class NewsEntryType implements StringPrimaryRecordInterface
{
    private string $id;
    private string $label;
    private UI_Icon $icon;

    public function __construct(string $id, string $label, UI_Icon $icon)
    {
        $this->id = $id;
        $this->label = $label;
        $this->icon = $icon;

        $this->icon->setTooltip($this->label);
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): UI_Icon
    {
        return $this->icon;
    }
}
