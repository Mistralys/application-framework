<?php
/**
 * @package Admin
 * @subpackage Welcome
 */

declare(strict_types=1);

namespace Application\OfflineEvents;

use Application\Admin\Welcome\Events\BaseWelcomeQuickNavListener;
use Application\Admin\Welcome\Screens\WelcomeArea;
use Application_EventHandler_Event;
use UI\Page\Navigation\QuickNavigation;

/**
 * Event fired to allow modification of the Quick Navigation on the
 * Welcome screen.
 *
 * @package Admin
 * @subpackage Welcome
 *
 * @see BaseWelcomeQuickNavListener
 */
class WelcomeQuickNavEvent extends Application_EventHandler_Event
{
    public const string EVENT_NAME = 'WelcomeQuickNav';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getScreen() : WelcomeArea
    {
        return $this->getArgumentObject(0, WelcomeArea::class);
    }

    public function getQuickNav() : QuickNavigation
    {
        return $this->getArgumentObject(1, QuickNavigation::class);
    }
}
