<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application_Admin_Area_Mode_CollectionCreate;
use Application_Exception;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use TestDriver\ClassFactory;
use TestDriver\TestDBCollection\TestDBRecord;
use TestDriver\TestDBCollection\TestSettingsManagerLegacy;
use TestDriver\TestDBCollection;

class CollectionCreateManagerLegacyScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    public const URL_NAME = 'collection-create-legacy';
    public const ERROR_INVALID_VALUES = 146701;

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
        return t('Create record - with settings manager');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This tests a legacy settings manager setup.')
            ->nl()
            ->t('Submit the form, and if everything works as expected, a success message will be shown.')
            ->t('Otherwise, and exception is thrown.');
    }

    protected function _handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        $data->requireNotEmpty(TestSettingsManagerLegacy::SETTING_GENERATE_ALIAS);

        $expected = TestSettingsManagerLegacy::PREFIX_GENERATED_ALIAS.$data->getKey(TestSettingsManagerLegacy::SETTING_GENERATE_ALIAS);
        $actual = $data->getKey(TestDBRecord::COL_ALIAS);

        if($actual === $expected) {
            $this->redirectWithSuccessMessage(
                'The data has been processed successfully.',
                $this->getURL()
            );
        }

        throw new Application_Exception(
            'Invalid values submitted.',
            sprintf(
                'Expected field [%s] to equal [%s], but got [%s].',
                TestDBRecord::COL_ALIAS,
                $expected,
                $actual
            ),
            self::ERROR_INVALID_VALUES
        );
    }
}
