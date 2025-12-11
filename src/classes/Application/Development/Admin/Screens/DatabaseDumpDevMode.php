<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Development\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\AppFactory;
use Application\Development\Admin\DevScreenRights;
use Application_DBDumps;
use AppUtils\Request_Exception;
use UI;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer;

/**
 * Developer helper to handle database dumps: creates dumps using
 * the bundled shell script (Linux only), and offers them for
 * download in the user interface, along with a history of previous
 * dumps.
 *
 * @package Application
 * @subpackage Administration
 * @author Andreas Martin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DatabaseDumpDevMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'dbdump';

    protected UI_DataGrid $datagrid;
    protected Application_DBDumps $dumps;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_DATABASE_DUMPS;
    }

    public function getTitle(): string
    {
        return t('Database dumps');
    }

    public function getNavigationTitle(): string
    {
        return t('Database dumps');
    }

    public function getDevCategory(): string
    {
        return t('Settings');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }

    protected function _handleActions(): bool
    {
        $this->dumps = AppFactory::createDBDumps();

        $this->createDataGrid();

        if ($this->request->getBool('confirm')) {
            $this->createDump();
        } else if ($this->request->getBool('download')) {
            $this->downloadDump();
        }

        return true;
    }

    /**
     * @return never
     * @throws Request_Exception
     */
    protected function downloadDump(): never
    {
        $id = $this->request->registerParam('dump_id')->setInteger()->setCallback(array($this->dumps, 'dumpExists'))->get();
        if (empty($id)) {
            $this->redirectWithInfoMessage(
                t('No such database dump found.'),
                $this->getURL()
            );
        }

        $dump = $this->dumps->getByID($id);
        $dump->sendFile();
    }

    protected function createDump(): void
    {
        $dump = $this->dumps->createDump();

        $this->ui->addSuccessMessage(t(
            'The dump %1$s has been successfully created at %2$s.',
            '<b>' . $dump->getID() . '</b>',
            date('H:i:s')
        ));

        if ($this->request->getBool('download')) {
            $dump->sendFile();
        }

        $this->redirectTo($this->getURL());
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        $dumps = $this->dumps->getAll();

        $entries = array();
        foreach ($dumps as $dump) {
            $entries[] = array(
                'selected' => $dump->getID(),
                'name' => '<a href="' . $dump->getURLDownload() . '">' . $dump->getDatePretty() . '</a>',
                'size' => $dump->getFileSizePretty()
            );
        }

        return $this->renderer
            ->makeWithSidebar()
            ->appendDataGrid(
                $this->datagrid,
                $entries
            );
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create', t('Create new dump'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->getURL(array('confirm' => 'yes')))
            ->makePrimary();

        $this->sidebar->addButton('create_dl', t('Create and download'))
            ->setIcon(UI::icon()->download())
            ->makeLinked($this->getURL(array('confirm' => 'yes', 'download' => 'yes')));

        if (isOSWindows()) {
            $this->sidebar->addSeparator();

            $this->sidebar->addInfoMessage(
                '<b>' . t('Note:') . '</b> ' .
                t(
                    'Please ensure that the %1$s executable is accessible in the system %2$s variable.',
                    '<code>mysqldump.exe</code>',
                    '<code>PATH</code>'
                )
            );
        }
    }

    private function createDatagrid(): void
    {
        $grid = $this->ui->createDataGrid('dumps');
        $grid->enableMultiSelect('selected');
        $grid->addColumn('selected', t('ID'))->setCompact();
        $grid->addColumn('name', t('Created on'));
        $grid->addColumn('size', t('Size'));
        $grid->addConfirmAction(
            'delete',
            t('Delete...'),
            t('The selected dumps will be deleted.') . ' ' . t('This cannot be undone, are you sure?')
        )
            ->makeDangerous()
            ->setIcon(UI::icon()->delete())
            ->setCallback(array($this, 'handle_multiDelete'));

        $this->datagrid = $grid;
    }

    public function handle_multiDelete(UI_DataGrid_Action $action): void
    {
        $deleted = 0;

        foreach ($action->getSelectedValues() as $id) {
            $dump = $this->dumps->getByID((int)$id);
            $dump->delete();
            $deleted++;
        }

        if ($deleted === 1) {
            $this->redirectWithSuccessMessage(
                t(
                    'The dump has been successfully deleted at %1$s.',
                    date('H:i:s')
                ),
                $this->getURL()
            );
        } else if ($deleted > 1) {
            $this->redirectWithSuccessMessage(
                t(
                    '%1$s dumps have been successfull deleted at %2$s.',
                    $deleted,
                    date('H:i:s')
                ),
                $this->getURL()
            );
        }

        $this->redirectWithInfoMessage(
            UI::icon()->information() . ' ' .
            '<b>' . t('No dumps deleted:') . '</b> ' .
            t('No dumps were selected.'),
            $this->getURL()
        );
    }
}
