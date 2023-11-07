<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application_Admin_Area_Mode_CollectionCreate;
use Application_Exception;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use TestDriver\ClassFactory;
use TestDriver\TestDBCollection;
use TestDriver\TestDBCollection\TestDBRecord;
use TestDriver\TestDBCollection\TestSettingsManagerExtended;

class CollectionCreateManagerExtendedScreen extends Application_Admin_Area_Mode_CollectionCreate
{
    public const URL_NAME = 'collection-create-manager-ex';
    public const ERROR_INVALID_VALUES = 146801;

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function createCollection() : TestDBCollection
    {
        return ClassFactory::createTestDBCollection();
    }

    public function getSettingsManager() : TestSettingsManagerExtended
    {
        return new TestSettingsManagerExtended($this, $this->record);
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
        return t('Create record - with extended settings manager');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This tests the collection settings with an extended settings manager instance.')
            ->nl()
            ->t('Submit the form, and if everything works as expected, a success message will be shown.')
            ->t('Otherwise, and exception is thrown.');
    }

    protected function _handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        $data->requireNotEmpty(TestSettingsManagerExtended::SETTING_GENERATE_ALIAS);
        $data->requireNotEmpty(TestDBRecord::COL_LABEL);
        $data->requireNotEmpty(TestDBRecord::COL_ALIAS);

        $expected = TestSettingsManagerExtended::PREFIX_GENERATED_ALIAS.$data->getKey(TestSettingsManagerExtended::SETTING_GENERATE_ALIAS);
        $actual = $data->getKey(TestDBRecord::COL_ALIAS);

        if($expected === $actual) {
            $this->redirectWithSuccessMessage(
                'The data has been processed successfully.',
                $this->getURL()
            );
        }

        throw new Application_Exception(
            'The generated alias does not match the expected value.',
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
