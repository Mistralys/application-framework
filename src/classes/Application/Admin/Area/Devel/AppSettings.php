<?php
/**
 * File containing the {@link Application_Admin_Area_Devel_AppSettings} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Area_Devel_AppSettings
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ConvertHelper;
use AppUtils\OutputBuffering;

/**
 * Developer helper that can be used to store settings in the application
 * settings table.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Devel_AppSettings extends Application_Admin_Area_Mode
{
    public const URL_NAME = 'appsettings';
    public const SETTING_TYPE_STRING = 'string';
    protected string $formName = 'devel_app_settings';
    protected UI_DataGrid $datagrid;
    protected string $elDataKeyID = '';
    protected string $elValueID = '';
    protected Application_FilterSettings_AppSettings $filterSettings;
    protected Application_FilterCriteria_AppSettings $filterCriteria;

    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $settings = array();


    public function getURLName() : string
    {
        return self::URL_NAME;
    }
    
    public function getTitle() : string
    {
        return t('Application settings');
    }
    
    public function getNavigationTitle() : string
    {
        return t('Application settings');
    }
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
    
    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }
    
    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }
    
    protected function _handleActions() : bool
    {
        $this->filterSettings = new Application_FilterSettings_AppSettings(self::URL_NAME);
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

        return true;
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
        ->makeWithSidebar();
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setTitle($this->getTitle())
            ->setAbstract(
                t('This allows managing custom application settings.').' '.
                t('They are not used by the application itself, but can be used to store arbitrary data for custom processes.')
            );
    }

    protected function _handleSidebar() : void
    {
        $this->sidebar->addFilterSettings($this->filterSettings);
        
        $formSection = $this->sidebar->addSection()
            ->setTitle('Add setting')
            ->setAbstract(t('Allows adding or overwriting values (when using the same data key).'))
            ->setContent($this->formableForm->renderHorizontal())
            ->collapse();

        $this->registerSettings();

        $this->sidebar->addSection()
            ->setTitle(t('Settings registry'))
            ->setAbstract(sb()
                ->t('These are known settings that can be used.')
                ->t('Click on a name to insert it into the form.')
            )
            ->setContent($this->renderRegistry($formSection))
            ->collapse();
    }

    private function registerSettings() : void
    {
        $this->registerSetting(
            UI_MarkupEditorInfo::SETTING_NAME_MARKUP_EDITOR_ID,
            self::SETTING_TYPE_STRING,
            t('The ID of the markup editor to use.')
        );

        $this->registerSetting(
            Application_Driver::APP_SETTING_KEEP_ALIVE_INTERVAL,
            self::SETTING_TYPE_STRING,
            (string)sb()
                ->t('The interval in which the authentication keep-alive process is run.')
                ->t(
                    'Accepts a duration parseable by PHP\'s %1$s function, e.g. %2$s.',
                    sb()->link('strtotime', 'https://www.php.net/manual/en/function.strtotime.php', true),
                    sb()->code('20 seconds')
                )
        );

        $this->_registerSettings();
    }

    abstract protected function _registerSettings() : void;

    protected function registerSetting(string $name, string $type, string $description='') : void
    {
        $this->settings[$name] = array(
            'name' => $name,
            'type' => $type,
            'description' => $description
        );
    }

    protected function renderRegistry(UI_Page_Section $formSection) : string
    {
        ksort($this->settings);

        OutputBuffering::start();
        ?>
            <ul class="unstyled">
            <?php
            foreach($this->settings as $def)
            {
                ?>
                <li style="padding-bottom: 6px">
                    <i><?php echo $def['type'] ?></i>
                    <code onclick="<?php echo $this->renderStatement($formSection, $def) ?>" class="clickable">
                        <?php echo $def['name'] ?>
                    </code>
                    <br>
                    <?php echo $def['description'] ?>
                </li>
                <?php
            }
            ?>
            </ul>
        <?php

        return OutputBuffering::get();
    }

    protected function renderStatement(UI_Page_Section $formSection, array $def) : string
    {
        return sprintf(
            "%s;$('#%s').val('%s');$('#%s').focus()",
            $formSection->getJSExpand(),
            $this->elDataKeyID,
            $def['name'],
            $this->elValueID
        );
    }
    
    protected function createEditForm() : void
    {
        $this->createFormableForm($this->formName);
        $this->formableForm->makeCondensed();
        $this->formableForm->removeClass('form-horizontal');
        
        $this->addFormablePageVars();
        
        $text = $this->addElementText('key', t('Data key'));
        $text->addClass('input-block');
        $text->addFilterTrim();
        $this->makeLengthLimited($text, 1, 160);
        $this->makeRequired($text);

        $this->elDataKeyID = $text->getId();
        
        $value = $this->addElementTextarea('value', t('Value'));
        $value->removeClass('input-xxlarge');
        $value->addClass('input-block');
        $value->addFilterTrim();
        $this->makeRequired($value);

        $this->elValueID = $value->getId();
        
        $this->formableForm->addPrimarySubmit(UI::icon()->add().' '.t('Add / overwrite setting'));
    }
    
    private function createDatagrid() : void
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
            sb()
                ->t('The selected values will be deleted.')
                ->cannotBeUndone()
        )
        ->makeDangerous()
        ->setIcon(UI::icon()->delete())
        ->setCallback(array($this, 'handle_multiDelete'));

        $grid->addHiddenScreenVars();
        $grid->enableLimitOptionsDefault();

        $this->datagrid = $grid;
    }
    
    public function handle_multiDelete(UI_DataGrid_Action $action) : void
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
                    '%1$s settings have been successfully deleted at %2$s.',
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
