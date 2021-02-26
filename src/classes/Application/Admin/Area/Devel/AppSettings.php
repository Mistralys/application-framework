<?php
/**
 * File containing the {@link Application_Admin_Area_Devel_AppSettings} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Area_Devel_AppSettings
 */

use AppUtils\ConvertHelper;

/**
 * Developer helper that can be used to store settings in the application
 * settings table.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_AppSettings extends Application_Admin_Area_Mode
{
   /**
    * @var string
    */
    protected $formName = 'devel_app_settings';
    
    /**
     * @var UI_DataGrid
     */
    protected $datagrid;
    
    public function getURLName()
    {
        return 'appsettings';
    }
    
    public function getTitle()
    {
        return t('Application settings');
    }
    
    public function getNavigationTitle()
    {
        return t('Application settings');
    }
    
    public function getDefaultSubmode()
    {
        return null;
    }
    
    public function isUserAllowed()
    {
        return $this->user->isDeveloper();
    }
    
    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }
    
   /**
    * @var Application_FilterSettings_AppSettings
    */
    protected $filterSettings;
    
   /**
    * @var Application_FilterCriteria_AppSettings
    */
    protected $filterCriteria;
    
    protected function _handleActions()
    {
        $this->filterSettings = new Application_FilterSettings_AppSettings('appsettings');
        $this->filterCriteria = new Application_FilterCriteria_AppSettings();
        
        $this->createEditForm();
        
        $this->createDatagrid();
        
        if($this->isFormValid())
        {
            $values = $this->formableForm->getValues();
            
            $exists = $this->filterCriteria->settingExists($values['key']);
            
            $this->startTransaction();
            $this->filterCriteria->addSetting($values['key'], $values['value']);
            $this->endTransaction();
            
            if($exists) {
                $this->redirectWithSuccessMessage(
                    t(
                        'The setting %1$s has been overwritten successfully at %2$s.',
                        $values['key'],
                        date('H:i:s')
                    ), 
                    $this->getURL()
                );
            }
            
            $this->redirectWithSuccessMessage(
                t(
                    'The setting %1$s has been added successfully at %2$s.',
                    $values['key'],
                    date('H:i:s')
                ), 
                $this->getURL()
            );
        }
    }
    
    protected function _renderContent()
    {
        $this->datagrid->configure($this->filterSettings, $this->filterCriteria);
        
        $items = $this->filterCriteria->getItems();
        $entries = array();

        $maxLength = 70;
        
        foreach($items as $item)
        {
            $value = $item['data_value'];
            $size = ConvertHelper::string2bytes($value);
            $length = mb_strlen($value);
            
            if($length > $maxLength)
            {
                $value = ConvertHelper::text_cut($value, $maxLength, ' [...]');
            }
            
            $entries[] = array(
                'data_value' => $value,
                'data_key' => $item['data_key'],
                'data_role' => $item['data_role'],
                'size' => ConvertHelper::bytes2readable($size)
            );
        }
        
        return $this->renderer
        ->appendDataGrid($this->datagrid, $entries)
        ->makeWithSidebar()
        ->setAbstract(
            t('This allows managing custom application settings.').' '.
            t('They are not used by the application itself, but can be used to store arbitrary data for custom processes.')
        );
    }
    
    protected function _handleSidebar()
    {
        $this->sidebar->addFilterSettings($this->filterSettings);
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addSection()
        ->setTitle('Add setting')
        ->setAbstract(t('Allows adding or overwriting values (when using the same data key).'))
        ->setContent($this->formableForm->renderHorizontal());
    }
    
    protected function createEditForm()
    {
        $this->createFormableForm($this->formName);
        $this->formableForm->makeCondensed();
        $this->formableForm->removeClass('form-horizontal');
        
        $this->addFormablePageVars();
        
        $text = $this->addElementText('key', t('Data key'));
        $text->addFilterTrim();
        $this->makeLengthLimited($text, 1, 160);
        $this->makeRequired($text);
        
        $value = $this->addElementTextarea('value', t('Value'));
        $value->addFilterTrim();
        $this->makeRequired($value);
        
        $this->formableForm->addPrimarySubmit(UI::icon()->add().' '.t('Add / overwrite setting'));
    }
    
    private function createDatagrid()
    {
        $grid = $this->ui->createDataGrid('custom_appsettings_grid');
        $grid->enableMultiSelect('data_key');
        $grid->addColumn('data_key', t('Data key'))->setCompact();
        $grid->addColumn('data_value', t('Value'));
        $grid->addColumn('size', t('Size'))->alignRight();
        $grid->addColumn('data_role', t('Role'));
        $grid->addConfirmAction(
            'delete',
            t('Delete...'),
            t('The selected values will be deleted.').' '.t('This cannot be undone, are you sure?')
        )
        ->makeDangerous()
        ->setIcon(UI::icon()->delete())
        ->setCallback(array($this, 'handle_multiDelete'));

        $grid->enableLimitOptionsDefault();
        
        $this->datagrid = $grid;
    }
    
    public function handle_multiDelete(UI_DataGrid_Action $action)
    {
        $ids = $action->getSelectedValues();
        
        $this->startTransaction();
        
        foreach($ids as $id)
        {
            $this->filterCriteria->deleteSetting($id);
        }
        
        $this->endTransaction();
        
        $total = count($ids);
        
        if ($total == 1)
        {
            $this->redirectWithSuccessMessage(
                t(
                    'The setting %1$s has been successfully deleted at %2$s.',
                    $ids[0],
                    date('H:i:s')
                ),
                $this->getURL()
            );
        }
        else if($total > 1)
        {
            $this->redirectWithSuccessMessage(
                t(
                    '%1$s settings have been successfull deleted at %2$s.',
                    $total,
                    date('H:i:s')
                ),
                $this->getURL()
            );
        }
        
        $this->redirectWithInfoMessage(
            UI::icon()->information().' '.
            '<b>'.t('No settings deleted:').'</b> '.
            t('No settings were selected.'),
            $this->getURL()
        );
    }
}