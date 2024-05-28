<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use DBHelper\BaseRecord\Event\KeyModifiedEvent;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver\TestDBRecords\TestDBCollection;

final class EventHandlingTests extends DBHelperTestCase
{
    public function test_unregisteredKeyModified(): void
    {
        $record = $this->createTestRecord('Old Label', 'old-alias');
        $called = false;

        $record->onKeyModified(function (KeyModifiedEvent $event) use (&$called): void {
            $called = true;

            $this->assertSame(TestDBCollection::COL_LABEL, $event->getKeyName());
            $this->assertFalse($event->isStructural(), 'The key is not registered, so the default is false.');
            $this->assertEmpty($event->getKeyLabel(), 'The key is not registered, so no label is available.');
            $this->assertSame('New Label', $event->getNewValue());
            $this->assertSame('Old Label', $event->getOldValue());
        });

        $record->setLabel('New Label');

        $this->assertTrue($called);
    }

    public function test_registeredKeyModified(): void
    {
        $record = $this->createTestRecord('Old Label', 'old-alias');
        $called = false;

        $record->onKeyModified(function (KeyModifiedEvent $event) use (&$called): void {
            $called = true;

            $this->assertSame(TestDBCollection::COL_ALIAS, $event->getKeyName());
            $this->assertTrue($event->isStructural(), 'The key is registered and set as structural.');
            $this->assertNotEmpty($event->getKeyLabel(), 'The key is registered, so a label is available.');
            $this->assertSame('new-alias', $event->getNewValue());
            $this->assertSame('old-alias', $event->getOldValue());
        });

        $record->setAlias('new-alias');

        $this->assertTrue($called);
    }
}
