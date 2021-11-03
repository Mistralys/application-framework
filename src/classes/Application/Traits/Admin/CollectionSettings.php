<?php
/**
 * File containing the {@see Application_Traits_Admin_CollectionSettings} trait.
 * 
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_CollectionSettings
 */

/**
 * Trait for administration screens that are used to
 * edit the settings of a DBHelper record. Handles 
 * fetching the record, building the form and all the
 * rest needed to handle the settings.
 *  
 * Works both with the edit and create screens.
 * 
 * Usage:
 * 
 * 1) Extend one of the premade screen classes (ex: Application_Admin_Area_Mode_Submode_CollectionEdit)
 * 2) Implement the abstract methods
 * 3) Implement any of the optional overridable methods
 *
 * After this, you can choose between handling form elements
 * manually in the admin screen, or if a SettingsManager can
 * be used instead, which further automates the process.
 *
 * Manual mode
 *
 * 4) Override the methods required when no settings manager is present
 * 5) You may need to use `_filterFormValues`
 * 6) You may need to use `_handleAfterSave`
 *
 * SettingsManager mode
 *
 * 4) Override the `getSettingsManager` method
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Area_Mode_Submode_CollectionEdit
 * @see Application_Admin_Area_Mode_Submode_Action_CollectionEdit
 * @see Application_Interfaces_Admin_CollectionSettings
 *
 * @property UI $ui
 * @property UI_Page_Sidebar $sidebar
 * @property Application_Request $request
 * @property Application_Driver $driver
 * @property Application_User $user
 * @property UI_Page $page
 * @property UI_Page_Breadcrumb $breadcrumb
 * @property Application_Session $session
 * @property Application_LockManager $lockManager
 * @property UI_Themes_Theme_ContentRenderer $renderer 
 * @property bool $adminMode
 * @property string $instanceID
 * @property UI_Form $formableForm
 * @method void createFormableForm(string $name, array $defaultData=array())
 * @method void startTransaction()
 * @method void endTransaction()
 */
trait Application_Traits_Admin_CollectionSettings
{
    /**
     * @var Application_Formable_RecordSettings|NULL
     */
    protected $settingsManager;

    /**
     * @var DBHelper_BaseRecord|NULL
     */
    protected $record;

    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;

    // region: Abstract methods

   /**
    * @return DBHelper_BaseCollection
    */
    abstract public function createCollection();

   /**
    * @param DBHelper_BaseRecord $record
    * @return string
    */
    abstract public function getSuccessMessage(DBHelper_BaseRecord $record) : string;
    
   /**
    * @return string
    */
    abstract public function getBackOrCancelURL() : string;

   /**
    * @return bool
    */
    abstract public function isEditMode() : bool;

    /**
     * Whether the user is allowed to edit the settings.
     * If not, the form is made readonly automatically.
     *
     * @return bool
     */
    abstract public function isUserAllowedEditing() : bool;

    // endregion

    /**
    * Retrieves the path to the administration screen that can be used to delete this item, if any.
    * @return string
    */
    protected function getDeletePath()
    {
        $path = 'delete';
        $parent = $this->getParentScreen();
        if($parent) {
            $path = $parent->getURLPath().'.'.$path;
        }
        
        return $path;
    }
    
   /**
    * Retrieves the administration screen that can be used to delete the record, if any.
    * @return Application_Admin_ScreenInterface|NULL
    */
    public function getDeleteScreen() : ?Application_Admin_ScreenInterface
    {
        $path = $this->getDeletePath();
        
        if($path) 
        {
            try
            {
                return $this->driver->getScreenByPath($path);
            } 
            catch(Exception $e) {}
        }
        
        return null;
    }
    
   /**
    * Retrieves the default form values to use for the form.
    * 
    * @return array<string,mixed>
    */
    public function getDefaultFormValues() : array
    {
        if(isset($this->settingsManager))
        {
            return $this->settingsManager->getDefaultValues();
        }

        if($this->isEditMode())
        {
            $data = array();
            $keys = $this->resolveKeyNames();

            foreach($keys as $key) {
                $data[$key] = $this->record->getRecordKey($key);
            }
            
            return $data;
        }
        
        return array();
    }

    private function resolveKeyNames(bool $includeVirtual=false) : array
    {
        if(isset($this->settingsManager))
        {
            return $this->settingsManager->getSettingKeyNames($includeVirtual);
        }

        return $this->getSettingsKeyNames();
    }

    public function getNavigationTitle() : string
    {
        return t('Settings');
    }
    
    public function getURLName() : string
    {
        if($this->isEditMode()) {
            return 'edit';
        }
        
        return 'create';
    }
    
    final protected function init() : void
    {
        $this->validateRequest();
        
        $this->collection = $this->createCollection();
    }
    
    public function getFormName() : string
    {
        $suffix = 'create';
        
        if($this->isEditMode())
        {
            $suffix = 'edit';
        }
        
        return $this->createCollection()->getRecordTypeName().'_settings_'.$suffix;
    }

    protected function _handleActions() : bool
    {
        if($this->isEditMode())
        {
            $this->record = $this->collection->getByRequest();

            if(!$this->record) {
                $this->redirectWithInfoMessage(
                    t('No such record found.'),
                    $this->getBackOrCancelURL()
                );
            }

            $this->logEvent('RecordLoaded', 'The record has been loaded successfully.');
            $this->_handleRecordLoaded();
        }

        $this->log('Setting up the form.');

        $this->createFormableForm($this->getFormName());
        $this->settingsManager = $this->getSettingsManager();
        $this->setDefaultFormValues($this->getDefaultFormValues());
        $this->addFormablePageVars();

        $this->log('Injecting all form elements.');

        if(isset($this->settingsManager))
        {
            $this->settingsManager->inject();
        }
        else
        {
            $this->injectFormElements();
        }
        
        if($this->isEditMode()) {
            $this->addHiddenVar(
                $this->collection->getRecordPrimaryName(), 
                (string)$this->record->getID()
            );
        }
        
        $parent = $this->collection->getParentRecord();
        if($parent !== null) 
        {
            $this->addHiddenVar(
                $parent->getRecordPrimaryName(),
                (string)$parent->getID()
            );
        }

        if($this->isReadonly())
        {
            $this->log('Switching the form to readonly mode.');

            $this->makeReadonly();
            return true;
        }
        
        if($this->isFormValid())
        {
            $this->handleFormSubmitted();
        }

        return true;
    }

    private function handleFormSubmitted() : void
    {
        $this->log('The form has been submitted and is valid.');

        $this->startTransaction();

        $data = new Application_Formable_RecordSettings_ValueSet($this->getSettingsFormValues());
        $filtered = $this->filterFormValues($data);

        if(!$this->isEditMode())
        {
            $record = $this->createRecord($filtered);
        }
        else
        {
            $record = $this->record;
            $this->saveRecord($filtered);
        }

        $this->handleAfterSave($record, $data);

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            $this->getSuccessMessage($record),
            $this->getSuccessURL($record)
        );
    }

    public function isReadonly() : bool
    {
        if(!$this->isUserAllowedEditing())
        {
            return true;
        }

        if(method_exists($this,'isEditable'))
        {
            return $this->isEditable() !== true;
        }

        return false;
    }
    
    public function getSuccessURL(DBHelper_BaseRecord $record) : string
    {
        if($this->isEditMode())
        {
            return $this->request->buildRefreshURL();
        }
        
        // Try to determine the target URL from the record instance
        $methods = array('getAdminSettingsURL', 'getAdminEditSettingsURL', 'getAdminEditURL');
        
        foreach($methods as $method)
        {
            if(method_exists($record, $method))
            {
                return $record->$method(array('created' => 'yes'));
            }
        }
        
        return $this->getBackOrCancelURL();
    }

    /**
     * Called once the record has been updated or created.
     *
     * The data set specified here is the unfiltered one
     * containing all form values, even the internal ones
     * that are not included in the data set given to the
     * record.
     *
     * @param DBHelper_BaseRecord $record
     * @param Application_Formable_RecordSettings_ValueSet $data
     */
    final protected function handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data) : void
    {
        $this->logEvent('AfterSave', 'The record has been created/updated.');

        if(isset($this->settingsManager))
        {
            $this->settingsManager->afterSave($record, $data);
        }

        $this->_handleAfterSave($record, $data);
    }

    /**
     * @return array<string,mixed>
     * @throws Application_Exception
     */
    public function getSettingsFormValues() : array
    {
        $keys = $this->resolveKeyNames(true);

        $values = $this->getFormValues();
        if(isset($this->settingsManager))
        {
            $values = array_merge($values, $this->settingsManager->getVirtualValues());
        }

        $result = array();
        foreach($keys as $keyName) 
        {
            if(isset($this->settingsManager))
            {
                $setting = $this->settingsManager->getSettingByName($keyName);

                // All values must be present, except static settings, which
                // do not necessarily have a value (they are cosmetic only,
                // like static form elements for displaying information)
                if ($setting->isStatic())
                {
                    continue;
                }
            }

            if(!array_key_exists($keyName, $values)) {
                throw new Application_Exception(
                    'Unknown setting key',
                    sprintf(
                        'The setting key [%s] was not found in the form values. Keys in the form are [%s], and registered keys are [%s].',
                        $keyName,
                        implode(', ', array_keys($values)),
                        implode(', ', $keys)
                    ),
                    Application_Interfaces_Admin_CollectionSettings::ERROR_UNKNOWN_SETTING_KEY
                );
            }

            $result[$keyName] = $values[$keyName];
        }
        
        return $result;
    }

    /**
     * Filters the values before they are applied to the record,
     * or used to create a new record.
     *
     * When not using a settings manager, the method `_filterFormValues()`
     * is called on the resulting form values.
     *
     * @param Application_Formable_RecordSettings_ValueSet $values
     * @return Application_Formable_RecordSettings_ValueSet
     * @throws Application_Exception
     */
    final protected function filterFormValues(Application_Formable_RecordSettings_ValueSet $values) : Application_Formable_RecordSettings_ValueSet
    {
        $this->log('Filtering the submitted form values.');

        $media = Application_Media::getInstance();
        
        $result = array();
        $data = $values->getValues();
        foreach($data as $name => $value)
        {
            if($media->isMediaFormValue($value)) {
                $value = $media->getByFormValue($value)->getID();
            }
            
            $result[$name] = $value;
        }

        if(isset($this->settingsManager))
        {
            return $this->settingsManager->filterForStorage(
                new Application_Formable_RecordSettings_ValueSet($result)
            );
        }

        return new Application_Formable_RecordSettings_ValueSet($this->_filterFormValues($result));
    }

    final protected function saveRecord(Application_Formable_RecordSettings_ValueSet $data) : void
    {
        $this->log('Updating the record.');

        $values = $data->getValues();

        foreach($values as $name => $value)
        {
            $this->record->setRecordKey($name, $value);
        }
        
        $this->record->save();
    }

    final protected function createRecord(Application_Formable_RecordSettings_ValueSet $data)
    {
        $this->log('Creating a new record.');

        return $this->collection->createNewRecord($data->getValues());
    }
    
    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle());
    }
    
    protected function _handleSidebar() : void
    {
        if(!$this->isUserAllowedEditing() || $this->isReadonly())
        {
            $this->sidebar->addButton('back', t('Back'))
            ->setIcon(UI::icon()->back())
            ->makeLinked($this->getBackOrCancelURL());
            return;
        }

        $this->_handleBeforeSidebar();

        if(!$this->isEditMode()) 
        {
            $this->sidebar->addButton('create-'.$this->collection->getRecordTypeName(), t('Create now'))
            ->makePrimary()
            ->setIcon(UI::icon()->add())
            ->makeClickableSubmit($this->formableForm);
        }
        else
        {
            $this->sidebar->addButton('edit-'.$this->collection->getRecordTypeName(), t('Save now'))
            ->makePrimary()
            ->setIcon(UI::icon()->save())
            ->makeClickableSubmit($this->formableForm);
        }
        
        $this->sidebar->addButton('cancel', $this->getCancelLabel())
        ->makeLinked($this->getBackOrCancelURL());
       
        if($this->isEditMode()) 
        {
            $screen = $this->getDeleteScreen();
            
            if($screen) 
            {
                $this->sidebar->addSeparator();
                
                $this->sidebar->addButton('delete-'.$this->collection->getRecordTypeName(), t('Delete...'))
                ->makeDangerous()
                ->setIcon(UI::icon()->delete())
                ->setTooltip(t('Opens a confirmation dialog to delete the record.'))
                ->makeConfirm($this->getDeleteConfirmMessage())
                ->makeLinked($this->resolveDeleteURL($screen))
                ->requireTrue($this->isUserAllowedEditing());
            }
        }
        
        $this->_handleAfterSidebar();

        $this->sidebar->addSeparator();
        
        $devel = $this->sidebar->addDeveloperPanel();
        
        $devel->addSubmitButton($this);
    }
    
    protected function resolveDeleteURL(Application_Admin_ScreenInterface $deleteScreen) : string
    {
        $params = $this->collection->getForeignKeys();
        $params[$this->collection->getRecordPrimaryName()] = $this->record->getID();
        
        return $deleteScreen->getURL($params);
    }
    
    public function getDeleteConfirmMessage() : string
    {
        return sb()
        ->bold(t('This will delete the item.'))
        ->para()
        ->danger(sb()->bold(t('Are you sure? This cannot be undone.')));
    }

    protected function resolveTitle() : string
    {
        $title = $this->getTitle();
        
        if(isset($this->record)) 
        {
            $title = $this->record->getLabel();
        }
        
        if($this->collection->hasParentCollection())
        {
            if(empty($title))
            {
                $title = $this->collection->getParentRecord()->getLabel();
            }
            else
            {
                $title = t(
                    '%1$s: %2$s',
                    $this->collection->getParentRecord()->getLabel(),
                    $title
                );
            }
        }
        
        return $title;
    }
    
    protected function _renderContent()
    {
        return $this->page->getRenderer()
        ->setTitle($this->resolveTitle())
        ->setAbstract($this->getAbstract())
        ->makeWithSidebar()
        ->appendForm($this->formableForm);
    }

    protected function requireMethod(string $methodName) : Application_Exception
    {
        return new Application_Exception(
            'Required method not implemented',
            sprintf(
                'The method %1$s must be implemented when not using a settings manager instance. '.
                'Either that, or implemement the getSettingsManager method instead.',
                $methodName
            ),
            Application_Interfaces_Admin_CollectionSettings::ERROR_MISSING_REQUIRED_METHOD
        );
    }

    final protected function traitGetCancelLabel() : string
    {
        return t('Cancel');
    }

    // region: Override: Optional

    public function getCancelLabel() : string
    {
        return $this->traitGetCancelLabel();
    }

    /**
     * Can optionally be overridden in the admin screen, to
     * filter the values before they are saved.
     *
     * NOTE: Only used when no settings manager is present.
     *
     * @param array<string,mixed> $values
     * @return array<string,mixed>
     */
    protected function _filterFormValues($values)
    {
        return $values;
    }

    /**
     * @return Application_Formable_RecordSettings|NULL
     */
    public function getSettingsManager()
    {
        return null;
    }

    protected function _handleBeforeSidebar()
    {

    }

    protected function _handleAfterSidebar()
    {

    }

    public function getAbstract() : string
    {
        return '';
    }

    /**
     * Called once the record has been created or saved,
     * and before the transaction is committed.
     *
     * NOTE: Only used if not using a settings manager.
     *
     * @param DBHelper_BaseRecord $record
     * @param Application_Formable_RecordSettings_ValueSet $data The form values that were submitted, including form-internal values.
     */
    protected function _handleAfterSave(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $data) : void
    {

    }

    protected function validateRequest() : void
    {

    }

    protected function _handleRecordLoaded() : void
    {

    }

    // endregion

    // region: Override: When no settings manager present


    /**
     * Used to inject the required form elements.
     *
     * NOTE: Only used if not using a settings manager.
     *
     * @throws Application_Exception
     */
    protected function injectFormElements()
    {
        throw $this->requireMethod('injectFormElements');
    }

    /**
     * Retrieves a list of form element names that must
     * be present in the form.
     *
     * NOTE: Only used if not using a settings manager.
     *
     * @return string[]
     */
    public function getSettingsKeyNames() : array
    {
        throw $this->requireMethod('getSettingsKeyNames');
    }

    // endregion
}
