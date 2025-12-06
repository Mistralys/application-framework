<?php

declare(strict_types=1);

namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;
use Application\AppFactory;
use Application\NewsCentral\NewsEntryCriticalities;
use AppLocalize\Localization\Locale\en_GB;

final class AlertTest extends NewsTestCase
{
    // region: _Tests

    public function test_createAlert() : void
    {
        $collection = AppFactory::createNews();

        $alert = $collection->createNewAlert(
            'Test alert',
            'en_UK',
            'Message',
            NewsEntryCriticalities::getInstance()->getWarning(),
            true
        );

        $this->assertSame('Test alert', $alert->getLabel());
        $this->assertSame('en_UK', $alert->getLocaleID());
        $this->assertInstanceOf(en_GB::class, $alert->getLocale());
        $this->assertSame('Message', $alert->getMessage());
        $this->assertDatesHaveBeenSet($alert);
    }

    // endregion
}
