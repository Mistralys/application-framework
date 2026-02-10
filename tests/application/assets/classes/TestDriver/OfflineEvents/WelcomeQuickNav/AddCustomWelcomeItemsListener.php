<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\WelcomeQuickNav;

use Application\Admin\Welcome\Events\BaseWelcomeQuickNavListener;
use Application\Admin\Welcome\Screens\WelcomeArea;
use UI\Page\Navigation\QuickNavigation;

class AddCustomWelcomeItemsListener extends BaseWelcomeQuickNavListener
{
    protected function configureScreen(WelcomeArea $screen, QuickNavigation $quickNav): void
    {
        $quickNav->addURL(t('Added via event'), APP_URL);
    }
}
