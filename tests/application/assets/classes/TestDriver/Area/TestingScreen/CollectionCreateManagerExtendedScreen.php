<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use TestDriver\TestDBRecords\TestDBCollection;
use Application_Admin_Area_Mode_CollectionCreate;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBRecord;
use TestDriver\TestDBRecords\TestSettingsManagerExtended;

/**
 * @see TestSettingsManagerExtended
 * @property TestDBRecord $record
 */
class CollectionCreateManagerExtendedScreen
    extends Application_Admin_Area_Mode_CollectionCreate
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const URL_NAME = 'collection-create-manager-ex';

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

    public static function getTestLabel() : string
    {
        return 'Create record - with extended settings manager';
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->add('This tests the collection settings with an extended settings manager instance.')
            ->nl()
            ->add('Submit the form, and if everything works as expected, a success message will be shown.')
            ->add('Otherwise, and exception is thrown.');
    }

    protected function _handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        TestSettingsManagerExtended::verifyValueSet($data);

        $this->redirectWithSuccessMessage(
            sprintf('The data has been processed successfully at %1$s.', sb()->time()),
            $this->getURL()
        );
    }
}
