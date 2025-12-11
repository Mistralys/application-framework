<?php

declare(strict_types=1);

namespace TestDriver\OfflineEvents\DisplayAppConfig;

use Application\Environments\Admin\Screens\AppConfigMode;
use Application\Environments\Events\BaseDisplayAppConfigListener;

class DisplayTestConfigListener extends BaseDisplayAppConfigListener
{
    protected function addGridEntries(AppConfigMode $screen): void
    {
        boot_define('TEST_CONFIG_SETTING', 'This is a test config setting value.');

        $screen->addHeader(t('Test configuration settings'));

        $screen->addConstant(t('Test setting'), 'TEST_CONFIG_SETTING');
    }
}
