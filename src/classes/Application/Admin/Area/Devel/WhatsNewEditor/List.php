<?php
/**
 * File containing the class {@see Application_Admin_Area_Devel_WhatsNewEditor}.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Devel_WhatsNewEditor
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\WhatsNew;
use Application\WhatsNew\AppVersion;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Admin_Area_Devel_WhatsNewEditor_List extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'list';
    public const COL_VERSION = 'version';

    private UI_DataGrid $grid;
    private WhatsNew $whatsNew;

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed() : bool
    {
        return $this->user->isDeveloper();
    }

    public function getNavigationTitle() : string
    {
        return t('Available versions');
    }

    public function getTitle() : string
    {
        return t('Available versions');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

    protected function _handleActions() : bool
    {
        $this->whatsNew = AppFactory::createWhatsNew();

        $this->createGrid();

        return true;
    }

    protected function _handleSidebar() : void
    {
        $this->sidebar->addButton('create-version', t('Create new version...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->whatsNew->getAdminCreateURL());
    }

    protected function _renderContent() : UI_Renderable_Interface
    {
        return $this->renderer
            ->appendDataGrid($this->grid, $this->collectEntries())
            ->makeWithSidebar();
    }

    private function collectEntries() : array
    {
        $versions = $this->whatsNew->getVersions();
        $result = array();

        foreach($versions as $version)
        {
            $result[] = $this->collectEntry($version);
        }

        return $result;
    }

    private function collectEntry(AppVersion $version) : array
    {
        return array(
            self::COL_VERSION => sb()->link(
                $version->getNumber(),
                $version->getAdminEditURL()
            )
        );
    }

    private function createGrid() : void
    {
        $grid = $this->ui->createDataGrid('whatsnew-versions-list');
        $grid->addColumn(self::COL_VERSION, t('Version'));
        
        $this->grid = $grid;
    }
}
