<?php

declare(strict_types=1);

namespace Application\Environments\Events;

use Application\Development\Events\DisplayAppConfigEvent;
use Application\Environments\Admin\Screens\AppConfigMode;
use Application\EventHandler\Event\EventInterface;
use Application\EventHandler\Event\StandardEvent;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use AppUtils\ClassHelper;

abstract class BaseDisplayAppConfigListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return DisplayAppConfigEvent::EVENT_NAME;
    }

    protected function handleEvent(EventInterface $event, ...$args): void
    {
        $this->addGridEntries(
            ClassHelper::requireObjectInstanceOf(
                DisplayAppConfigEvent::class,
                $event
            )
                ->getScreen()
        );
    }

    /**
     * Use the screen's methods to add entries to the configuration grid:
     *
     * - {@see AppConfigMode::addHeader()}
     * - {@see AppConfigMode::addConstant()}
     * - {@see AppConfigMode::addConstantBool()}
     *
     * @param AppConfigMode $screen
     * @return void
     */
    abstract protected function addGridEntries(AppConfigMode $screen): void;
}
