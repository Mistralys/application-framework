<?php
/**
 * @package TestDriver
 * @subpackage Testing
 */

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;
use TestDriver\TestDBRecords\TestDBCollection;
use Application_Formable_RecordSettings_ValueSet;
use TestDriver\ClassFactory;

/**
 * Tests the basic record creation without settings manager:
 * In this case, the form elements must be injected manually
 * here in this class.
 *
 * The form element names must match the database columns,
 * as the values are passed on directly to the DB collection.
 *
 * Using this approach should be avoided in favor of the
 * settings manager approach, as it has several drawbacks:
 *
 * 1. The setting names must match database column names
 * 2. Create and edit forms will duplicate much code
 * 3. Create and edit forms will have to be maintained separately
 *
 * @package TestDriver
 * @subpackage Testing
 */
class CollectionCreateBasicScreen
    extends BaseRecordCreateMode
    implements TestingScreenInterface
{
    use TestingScreenTrait;

    public const string URL_NAME = 'collection-create-basic';

    public function createCollection() : TestDBCollection
    {
        return ClassFactory::createTestDBCollection();
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
        return t('Create record - without settings manager');
    }

    public function getDefaultFormValues(): array
    {
        return array(
            TestDBCollection::COL_LABEL => 'Test label',
            TestDBCollection::COL_ALIAS => 'test-alias'
        );
    }

    protected function injectFormElements() : void
    {
        $this->injectLabel();
        $this->injectAlias();
    }

    private function injectLabel() : void
    {
        $el = $this->addElementText(TestDBCollection::COL_LABEL, t('Label'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 180);
        $this->makeRequired($el);
    }

    private function injectAlias() : void
    {
        $el = $this->addElementText(TestDBCollection::COL_ALIAS, t('Alias'));
        $el->addFilterTrim();
        $el->addClass('input-xlarge');
        $this->addRuleAlias($el);
        $this->makeLengthLimited($el, 0, 160);
        $this->makeRequired($el);
    }

    public function getSettingsKeyNames(): array
    {
        return array(
            TestDBCollection::COL_LABEL,
            TestDBCollection::COL_ALIAS
        );
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This tests the manual collection settings handling, without a settings manager instance.')
            ->nl()
            ->t('Submit the form, and if everything works as expected, a success message will be shown.')
            ->t('Otherwise, and exception is thrown.');
    }

    protected function _handleAfterSave(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $data): void
    {
        $data->requireNotEmpty(TestDBCollection::COL_LABEL);
        $data->requireNotEmpty(TestDBCollection::COL_ALIAS);

        $this->redirectWithSuccessMessage(
            sprintf('The data has been processed successfully at %1$s.', sb()->time()),
            $this->getURL()
        );
    }
}
