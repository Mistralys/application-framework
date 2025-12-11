<?php

declare(strict_types=1);

namespace UI\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;
use AppUtils\OutputBuffering;
use UI;
use UI\CSSGenerator\CSSGen;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer;

class CSSGenDevelMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'css-gen';
    public const string COL_NAME = 'name';
    public const string COL_LOCATION = 'location';
    public const string COL_FILE_ID = 'file_id';
    public const string COL_STATUS = 'status';
    public const string COL_PATH = 'path';
    public const string REQUEST_PARAM_GENERATE_ALL = 'generate';

    private CSSGen $cssGen;
    private UI_DataGrid $grid;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_CSS_GENERATOR;
    }

    public function getNavigationTitle(): string
    {
        return t('CSS Generator');
    }

    public function getTitle(): string
    {
        return t('CSS Generator');
    }

    public function getDevCategory(): string
    {
        return t('Tools');
    }

    protected function _handleActions(): bool
    {
        $this->cssGen = CSSGen::create();

        if($this->request->getBool(self::REQUEST_PARAM_GENERATE_ALL)) {
            $this->handleGenerateAll();
        }

        $this->createGrid();

        return true;
    }

    private function handleGenerateAll() : void
    {
        $this->cssGen->generateAll();

        $this->redirectWithSuccessMessage(
            t(
                'All CSS files have been generated successfully at %1$s.',
                sb()->time()
            ),
            $this->cssGen->getAdminURL()
        );
    }

    private function handleGenerateSelected(UI_DataGrid_Action $action) : void
    {
        $message = $action->createRedirectMessage($this->cssGen->getAdminURL())
            ->none(t('No CSS files selected that could be generated.'))
            ->single(t('The CSS file %1$s has been generated successfully at %2$s.', '$label', '$time'))
            ->multiple(t('%1$s CSS files have been generated successfully at %2$s.', '$amount', '$time'));

        foreach($action->getSelectedValues() as $id) {
            $message->addAffected($this->cssGen->getByID((string)$id)->generate()->getName());
        }

        $message->redirect();
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getTitle())
            ->setIcon(UI::icon()->css());

        $this->renderer
            ->setAbstract(sb()
                ->t(
                    'This tool can be used to compile the CSS template files (%1$s) to production files.',
                    sb()->code('.'.$this->cssGen::CSS_TEMPLATE_EXTENSION)
                )
                ->t('It processes the framework\'s own files, as well as those of the application, if any.')
            );
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('generate', t('Generate all'))
            ->setIcon(UI::icon()->generate())
            ->makePrimary()
            ->makeLinked($this->cssGen->getAdminGenerateURL());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendDataGrid($this->grid, $this->compileEntries())
            ->appendContent($this->renderLocationList())
            ->makeWithSidebar();
    }

    private function renderLocationList() : string
    {
        $folders = $this->cssGen->getLocations();
        $list = array();

        foreach($folders as $folder) {
            $list[] = sb()
                ->bold($folder->getLabel())
                ->nl()
                ->code($folder->getCSSFolder()->getRealPath());
        }

        OutputBuffering::start();
        ?>
        <hr>
        <p>
            <?php pt('CSS template files are searched for recursively in the following theme locations:') ?>
        </p>
        <?php echo sb()->ul($list);

        return OutputBuffering::get();
    }

    private function compileEntries() : array
    {
        $entries = array();
        $items = $this->cssGen->getAll();

        foreach($items as $item) {
            $entries[] = array(
                self::COL_FILE_ID => $item->getID(),
                self::COL_NAME => $item->getName(),
                self::COL_LOCATION => $item->getLocation()->getLabel(),
                self::COL_PATH => $item->getRelativePath(),
                self::COL_STATUS => $item->getStatusPretty()
            );
        }

        $this->grid->filterAndSortEntries($entries);

        return array_slice($entries, $this->grid->getOffset(), $this->grid->getLimit());
    }

    private function createGrid() : void
    {
        $grid = $this->ui->createDataGrid('cssgen-template-files');

        $grid->addColumn(self::COL_NAME, t('File'))
            ->setSortingString();

        $grid->addColumn(self::COL_LOCATION, t('Location'))
            ->setSortingString();

        $grid->addColumn(self::COL_STATUS, t('Status'))
            ->setSortingString();

        $grid->addColumn(self::COL_PATH, t('Path'))
            ->setSortingString();

        $grid->enableLimitOptionsDefault();
        $grid->enableMultiSelect(self::COL_FILE_ID);
        $grid->addHiddenScreenVars();

        $grid->addAction('generate', t('Generate'))
            ->setIcon(UI::icon()->generate())
            ->setCallback($this->handleGenerateSelected(...));

        $this->grid = $grid;
    }
}
