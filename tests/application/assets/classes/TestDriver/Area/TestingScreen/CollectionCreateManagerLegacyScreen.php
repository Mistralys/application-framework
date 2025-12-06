<?php

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use TestDriver\TestDBRecords\TestDBCollection;
use Application_Formable_RecordSettings_ValueSet;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestSettingsManagerLegacy;
use testsuites\DBHelper\RecordTest;

/**
 * @see TestSettingsManagerLegacy
 */
class CollectionCreateManagerLegacyScreen
    extends BaseRecordCreateMode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const string URL_NAME = 'collection-create-legacy';

    public function getRequiredRight(): ?string
    {
        return null;
    }

    public function createCollection() : TestDBCollection
    {
        return ClassFactory::createTestDBCollection();
    }

    public function getSettingsManager() : TestSettingsManagerLegacy
    {
        return new TestSettingsManagerLegacy($this, $this->createCollection(), $this->record);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return 'Success';
    }

    public function getBackOrCancelURL(): string
    {
        return $this->getURL();
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

    protected function _handleAfterSave(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        TestSettingsManagerLegacy::verifyDataSet($data);

        $this->redirectWithSuccessMessage(
            sprintf('The data has been processed successfully at %1$s.', sb()->time()),
            $this->getURL()
        );
    }
}
