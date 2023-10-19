<?php
/**
 * File containing the {@see Application_Traits_Admin_Wizard_CreateDBRecordStep} class.
 *
 * @package Application
 * @subpackage Traits
 * @see Application_Traits_Admin_Wizard_CreateDBRecordStep
 */

declare(strict_types=1);

use AppUtils\BaseException;
use AppUtils\ConvertHelper_Exception;

/**
 * Step in a record creation wizard: Confirm the selection, and
 * create the record.
 *
 * @package Application
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizard_CreateDBRecordStep
 * @see Application_Traits_Stubs_Admin_Wizard_CreateDBRecordStub
 */
trait Application_Traits_Admin_Wizard_CreateDBRecordStep
{
    // region: Step abstract methods

    abstract public function createCollection() : DBHelper_BaseCollection;

    abstract public function createSettingsManager() : Application_Formable_RecordSettings_Extended;

    abstract protected function configurePropertiesGrid(UI_PropertiesGrid $grid) : void;

    /**
     * @return array<string,mixed>
     */
    abstract protected function getSettingValues() : array;

    // endregion

    /**
     * @var Application_Formable_RecordSettings_Extended
     */
    private $settingsManager;

    public function getLabel() : string
    {
        return t('Confirmation');
    }

    public function getIcon() : ?UI_Icon
    {
        return UI::icon()->ok();
    }

    public function getAbstract() : string
    {
        return t('Please make sure that your selection is correct.');
    }

    protected function getDefaultData() : array
    {
        return array(
            Application_Interfaces_Admin_Wizard_CreateDBRecordStep::KEY_RECORD_ID => null
        );
    }

    /**
     * @return DBHelper_BaseRecord
     * @throws Application_Exception
     * @throws DBHelper_Exception
     */
    public function getRecord() : DBHelper_BaseRecord
    {
        $collection = $this->createCollection();

        $id = (int)$this->getDataKey(Application_Interfaces_Admin_Wizard_CreateDBRecordStep::KEY_RECORD_ID);
        if(!empty($id) && $collection->idExists($id)) {
            return $collection->getByID($id);
        }

        throw new Application_Exception(
            'No record instance has been created yet.',
            '',
            Application_Interfaces_Admin_Wizard_CreateDBRecordStep::ERROR_NO_RECORD_CREATED_YET
        );
    }

    public function _process() : bool
    {
        $this->settingsManager = $this->createSettingsManager();

        $this->createFormableForm('create_record_'.$this->createCollection()->getRecordTypeName());

        $this->injectNavigationButtons();

        if($this->isFormValid())
        {
            $this->setData(
                Application_Interfaces_Admin_Wizard_CreateDBRecordStep::KEY_RECORD_ID,
                $this->createRecord()->getID()
            );

            return true;
        }

        return false;
    }

    public function render() : string
    {
        $tbl = $this->ui->createPropertiesGrid();

        $this->configurePropertiesGrid($tbl);

        return $tbl.$this->renderFormable();
    }

    protected function getButtonConfirmLabel() : string
    {
        return t('Create now');
    }

    protected function getButtonConfirmTooltip() : string
    {
        return t(
            'Creates the %1$s with the selected settings.',
            $this->createCollection()->getRecordLabel()
        );
    }

    /**
     * Creates the mailing and the initial audience.
     *
     * @return DBHelper_BaseRecord
     * @throws BaseException
     */
    protected function createRecord() : DBHelper_BaseRecord
    {
        $this->log(sprintf('Creating the %s record.', $this->createCollection()->getRecordTypeName()));

        $record = $this->settingsManager->createRecord();

        $this->log(sprintf('Created record with ID [%s].', $record->getID()));

        return $record;
    }
}
