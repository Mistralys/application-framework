<?php
/**
 * @package User Interface
 * @subpackage Data Grids
 */

declare(strict_types=1);

namespace UI\DataGrid;

use Application\AppFactory;
use Application_Request;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\OutputBuffering;
use DBHelper;
use UI;
use UI_DataGrid;
use UI_DataGrid_Exception;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;
use function AppUtils\parseVariable;

/**
 * Handles the configuration UI for a data grid.
 *
 * When enabled, it replaces the data grid in situ
 * with a configuration interface that allows the user
 * to reorder columns and toggle their visibility.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class GridConfigurator implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const ERROR_MISSING_INVALID_COLUMNS = 162801;

    public const REQUEST_PARAM_SAVE = 'save-configuration';
    public const REQUEST_PARAM_COLUMNS = 'columns';
    public const REQUEST_PARAM_VISIBILITY = 'visibility';
    public const REQUEST_PARAM_RESET_GRID = 'reset_data_grid';

    private UI_DataGrid $grid;
    private UI $ui;
    private Application_Request $request;
    private bool $processed = false;

    public function __construct(UI_DataGrid $grid)
    {
        $this->grid = $grid;
        $this->ui = $grid->getUI();
        $this->request = AppFactory::createRequest();
    }

    public function getUI(): UI
    {
        return $this->ui;
    }

    public function getSectionID() : string
    {
        return $this->grid->getID().'_configurator';
    }

    public function process() : bool
    {
        if($this->request->getBool(self::REQUEST_PARAM_SAVE)) {
            $this->handleSaveSettings();
        }

        if($this->request->getBool(self::REQUEST_PARAM_RESET_GRID)) {
            $this->handleResetSettings();
        }

        return false;
    }

    public function render(): string
    {
        $this->process();

        $this->ui->addStylesheet('ui/datagrid/grid-configurator.css');

        $idBase = nextJSID();
        $idContainer = $idBase.'-container';

        $this->ui->addJavascriptOnload(sprintf(
            "$('#%s').sortable(%s);",
            $idContainer,
            JSONConverter::var2json(array(
                'handle' => '.grid-column-handle',
                'axis' => 'y',
            ))
        ));

        $section = $this->ui->createSection()
            ->setTitle(t('Grid configurator'))
            ->setID($this->getSectionID())
            ->setIcon(UI::icon()->settings())
            ->setAbstract(sb()
                ->t('The configurator allows you to customize the data grid to your liking.')
                ->t('Reorder the columns to your preference, and select which ones you want to be visible.')
            );

        OutputBuffering::start();
        ?>
        <form method="post">
            <?php echo $this->grid->renderHiddenVars() ?>
            <div class="configurator-hiddenvars hiddens">
                <input type="hidden" name="<?php echo UI_DataGrid::REQUEST_PARAM_CONFIGURE_GRID ?>" value="yes">
            </div>
            <div id="<?php echo $idContainer ?>" class="grid-configurator">
                <?php
                foreach($this->grid->getAllColumns() as $column)
                {
                    if($column->isAction() || $column->isHiddenByOption()) {
                        continue;
                    }

                    ?>
                    <div class="grid-column">
                        <input type="hidden" name="<?php echo self::REQUEST_PARAM_COLUMNS ?>[]" value="<?php echo $column->getDataKey() ?>">
                        <ul>
                            <li class="grid-column-handle"><?php echo UI::icon()->changeOrder() ?></li>
                            <li class="grid-column-label">
                            <?php echo sb()
                                ->italic($column->getTitle())
                                ->muted(sb()->parentheses(sb()->sf('#%02d', $column->getNumber())))
                            ->add($column->getOrder())
                            ?>
                            </li>
                            <li class="grid-column-visibility">
                                <?php
                                $checked = '';
                                if(!$column->isHiddenForUser()) {
                                    $checked = 'checked';
                                }
                                ?>
                                <label class="checkbox">
                                    <?php pt('Visible') ?>
                                    <input type="checkbox" name="<?php echo self::REQUEST_PARAM_VISIBILITY ?>[<?php echo $column->getDataKey() ?>]" value="yes" <?php echo $checked; ?>>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <?php
                }
                ?>
            </div>
            <hr>
            <p>
                <?php echo sb()
                    ->add(UI::button(t('Save grid settings'))
                        ->setIcon(UI::icon()->save())
                        ->makePrimary()
                        ->setTooltip(t('Save and use the current configuration.'))
                        ->makeSubmit(self::REQUEST_PARAM_SAVE, 'yes')
                    )
                    ->add(UI::button(t('Cancel'))
                        ->setIcon(UI::icon()->cancel())
                        ->setTooltip(t('Returns to the grid without saving changes.'))
                        ->link($this->grid->getRefreshURL())
                    )
                    ->add('&#160;|&#160;')
                    ->add(UI::button(t('Reset to default'))
                        ->setIcon(UI::icon()->reset())
                        ->makeWarning()
                        ->setTooltip(t('Resets the grid to its default configuration.'))
                        ->makeSubmit(self::REQUEST_PARAM_RESET_GRID, 'yes')
                    );
                ?>
            </p>
        </form>
        <?php

        return (string)$section->setContent(OutputBuffering::get());
    }

    private function handleSaveSettings() : void
    {
        $columns = $this->request->getParam(self::REQUEST_PARAM_COLUMNS);

        if(!is_array($columns)) {
            throw new UI_DataGrid_Exception(
                'No columns specified.',
                sprintf(
                    'The columns list was not an array in the request. '.PHP_EOL.
                    'Given: '.PHP_EOL.
                    '%s',
                    parseVariable($columns)->enableType()->toString()
                ),
                self::ERROR_MISSING_INVALID_COLUMNS
            );
        }

        // Use the same starting index as the columns
        // in the grid for maximum compatibility.
        $index = UI_DataGrid::COLUMN_START_INDEX;

        foreach($columns as $columnName) {
            if($this->grid->hasColumn($columnName)) {
                $this->grid->requireColumnByName($columnName)->setOrder($index);
            }

            $index++;
        }

        $visibility = $this->request->getParam(self::REQUEST_PARAM_VISIBILITY);
        if(!is_array($visibility)) {
            $visibility = array();
        }

        foreach($this->grid->getAllColumns() as $column) {
            if($column->isAction() || $column->isHiddenByOption()) {
                continue;
            }
            $visible = string2bool($visibility[$column->getDataKey()] ?? 'no');
            $column->setHiddenForUser(!$visible);
        }

        AppFactory::createDriver()->redirectWithSuccessMessage(
            t('The grid configuration has been saved.'),
            $this->grid->getRefreshURL()
        );
    }

    private function handleResetSettings() : void
    {
        DBHelper::startTransaction();
            $this->grid->resetSettings();
        DBHelper::commitTransaction();

        AppFactory::createDriver()->redirectWithSuccessMessage(
            t('The grid configuration has been reset successfully at %1$s.', sb()->time()),
            $this->grid->getRefreshURL()
        );
    }
}
