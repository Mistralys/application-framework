<?php

declare(strict_types=1);

namespace NewsCentral;

use Application\NewsCentral\NewsEntryStatuses;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use UI_Badge;
use UI_Icon;

class NewsEntryStatus implements StringPrimaryRecordInterface
{
    private string $id;
    private string $label;
    private UI_Badge $badge;
    private UI_Icon $icon;

    public function __construct(string $id, string $label, UI_Badge $badge, UI_Icon $icon)
    {
        $this->id = $id;
        $this->label = $label;
        $this->badge = $badge;
        $this->icon = $icon;

        $this->badge->setLabel($this->getLabel());
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIconLabel() : string
    {
        return (string)sb()
            ->add($this->getIcon())
            ->add($this->getLabel());
    }

    public function getBadge() : UI_Badge
    {
        return $this->badge;
    }

    public function getIcon(): UI_Icon
    {
        return $this->icon;
    }

    public function isPublished() : bool
    {
        return $this->id === NewsEntryStatuses::STATUS_PUBLISHED;
    }
}
