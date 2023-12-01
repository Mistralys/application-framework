<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application_Admin_Area_Mode_CollectionCreate;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use TestDriver\ClassFactory;
use TestDriver\TestDBCollection\TestSettingsManagerLegacy;
use TestDriver\TestDBCollection;

/**
 * @see TestSettingsManagerLegacy
 */
class CollectionCreateManagerLegacyScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    public const URL_NAME = 'collection-create-legacy';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function createCollection() : TestDBCollection
    {
        return ClassFactory::createTestDBCollection();
    }

    public function getSettingsManager() : TestSettingsManagerLegacy
    {
        return new TestSettingsManagerLegacy($this, $this->createCollection(), $this->record);
    }

    public function getSuccessMessage(DBHelper_BaseRecord $record): string
    {
        return 'Success';
    }

    public function getBackOrCancelURL(): string
    {
        return $this->getURL();
    }

    public function isUserAllowed(): bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle(): string
    {
        return self::getTestLabel();
    }

    public function getTitle(): string
    {
        return self::getTestLabel();
    }

    public static function getTestLabel() : string
    {
        return 'Create record - with settings manager';
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->add('This tests a legacy settings manager setup.')
            ->nl()
            ->add('Submit the form, and if everything works as expected, a success message will be shown.')
            ->add('Otherwise, and exception is thrown.');
    }

    protected function _handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        TestSettingsManagerLegacy::verifyDataSet($data);

        $this->redirectWithSuccessMessage(
            sprintf('The data has been processed successfully at %1$s.', sb()->time()),
            $this->getURL()
        );
    }
}
