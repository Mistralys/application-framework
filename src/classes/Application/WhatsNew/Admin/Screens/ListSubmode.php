<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\WhatsNew\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeInterface;
use Application\WhatsNew\Admin\Traits\WhatsNewSubmodeTrait;
use Application\WhatsNew\Admin\WhatsNewScreenRights;
use Application\WhatsNew\AppVersion;
use Application\WhatsNew\WhatsNew;
use UI;
use UI_DataGrid;
use UI_Renderable_Interface;

/**
 * User interface for editing the `WHATSNEW.xml` file.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ListSubmode extends BaseSubmode implements WhatsNewSubmodeInterface
{
    use WhatsNewSubmodeTrait;

    public const string URL_NAME = 'list';
    public const string COL_VERSION = 'version';

    private UI_DataGrid $grid;
    private WhatsNew $whatsNew;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): ?string
    {
        return WhatsNewScreenRights::SCREEN_LIST;
    }

    public function getNavigationTitle(): string
    {
        return t('Available versions');
    }

    public function getTitle(): string
    {
        return t('Available versions');
    }

    protected function _handleActions(): bool
    {
        $this->whatsNew = AppFactory::createWhatsNew();

        $this->createGrid();

        return true;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-version', t('Create new version...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->whatsNew->getAdminCreateURL());
    }

    protected function _renderContent(): UI_Renderable_Interface
    {
        return $this->renderer
            ->appendDataGrid($this->grid, $this->collectEntries())
            ->makeWithSidebar();
    }

    private function collectEntries(): array
    {
        $versions = $this->whatsNew->getVersions();
        $result = array();

        foreach ($versions as $version) {
            $result[] = $this->collectEntry($version);
        }

        return $result;
    }

    private function collectEntry(AppVersion $version): array
    {
        return array(
            self::COL_VERSION => sb()->link(
                $version->getNumber(),
                $version->getAdminEditURL()
            )
        );
    }

    private function createGrid(): void
    {
        $grid = $this->ui->createDataGrid('whatsnew-versions-list');
        $grid->addColumn(self::COL_VERSION, t('Version'));

        $this->grid = $grid;
    }
}
