<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans;

use Application\AppFactory;
use DateTime;
use UI_Page_Sidebar;

class SidebarSpans
{
    private DateTime $date;
    private UI_Page_Sidebar $sidebar;

    public function __construct(DateTime $targetDate, UI_Page_Sidebar $sidebar)
    {
        $this->date = $targetDate;
        $this->sidebar = $sidebar;
    }

    public function addItems() : void
    {
        $spans = AppFactory::createTimeTracker()
            ->createTimeSpans()
            ->getFilterCriteria()
            ->selectDate($this->date)
            ->getItemsObjects();

        if(empty($spans)) {
            return;
        }

        $this->sidebar->addSection()
            ->setTitle(t('Time Spans'))
            ->setAbstract(t('Some time spans intersect with this day:'))
            ->expand()
            ->setContent($this->renderSpans($spans));
    }

    /**
     * @param TimeSpanRecord[] $spans
     * @return string
     */
    private function renderSpans(array $spans) : string
    {
        $list = $this->sidebar->getUI()
            ->createBigSelection()
            ->makeSmall();

        foreach ($spans as $span) {
            $list->addItem($span->getLabelLinked());
        }

        return (string)$list;
    }
}
