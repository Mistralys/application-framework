<?php
/**
 * @package Admin
 * @subpackage Welcome
 */

declare(strict_types=1);

namespace Application\Admin\Welcome\Events;

use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\EventHandler\OfflineEvents\BaseOfflineListener;
use Application_EventHandler_Event;
use AppUtils\ClassHelper;
use UI\Page\Navigation\QuickNavigation;

/**
 * Abstract base class to implement a listener for the Welcome screen's
 * Quick Navigation configuration event, {@see WelcomeQuickNavEvent}.
 *
 * @package Admin
 * @subpackage Welcome
 */
abstract class BaseWelcomeQuickNavListener extends BaseOfflineListener
{
    public function getEventName(): string
    {
        return WelcomeQuickNavEvent::EVENT_NAME;
    }

    protected function handleEvent(Application_EventHandler_Event $event, ...$args): void
    {
        $welcome = ClassHelper::requireObjectInstanceOf(
            WelcomeQuickNavEvent::class,
            $event
        );

        $this->configureScreen(
            $welcome->getScreen(),
            $welcome->getQuickNav()
        );
    }

    abstract protected function configureScreen(WelcomeArea $screen, QuickNavigation $quickNav): void;
}