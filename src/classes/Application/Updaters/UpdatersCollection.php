<?php
/**
 * @package Maintenance
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\Updaters;

use Application\Themes\Default\Templates\Updaters\UpdatersScaffoldTmpl;
use Application\Themes\Default\Templates\Updaters\UpdatersSelectionTmpl;
use Application_Bootstrap_Screen_Updaters;
use Application_Driver;
use Application_Request;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use UI;
use UI_Themes_Theme;

/**
 * UI generator for the available maintenance scripts. Displays the
 * script details and dispatches actions to the selected scripts ("updaters").
 *
 * @package Maintenance
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UpdaterInterface[] getAll()
 * @method UpdaterInterface getByID(string $id)
 */
class UpdatersCollection extends BaseClassLoaderCollectionMulti
{
    public const string REQUEST_PARAM_UPDATER_ID = 'updater_id';

    /**
     * @var FolderInfo[]
     */
    protected array $classFolders;
    protected Application_Driver $driver;
    protected Application_Request $request;
    protected UI $ui;
    protected UI_Themes_Theme $theme;

    public function __construct()
    {
        $this->driver = Application_Driver::getInstance();

        if (!$this->driver->getUser()->isDeveloper()) {
            $this->driver->redirectWithInfoMessage(
                '<b>' . t('Note:') . '</b> ' . t('The maintenance tools are reserved for developers.'),
                APP_URL
            );
        }

        $this->classFolders = array(
            FolderInfo::factory($this->driver->getClassesFolder() . '/Updaters'),
            FolderInfo::factory(__DIR__.'/Bundled')
        );

        $this->request = $this->driver->getRequest();
        $this->ui = $this->driver->getUI();
        $this->theme = $this->ui->getTheme();
    }

    protected ?UpdaterInterface $activeUpdater = null;

    public function start(): void
    {
        $updaterID = $this->request->getParam(self::REQUEST_PARAM_UPDATER_ID);

        if (!empty($updaterID)) {
            $this->activeUpdater = $this->getByID($updaterID);
            echo $this->activeUpdater->start();
            return;
        }

        $this->showSelectionScreen();
    }

    /**
     * Retrieves all updater scripts for the specified version, if any.
     *
     * @param string $version
     * @return UpdaterInterface[]
     */
    public function getForVersion(string $version): array
    {
        $result = array();
        foreach ($this->getAll() as $updater) {
            if ($updater->hasSpecificVersion($version)) {
                $result[] = $updater;
            }
        }

        return $result;
    }

    /**
     * @param UpdaterInterface $a
     * @param UpdaterInterface $b
     * @return int
     */
    protected function sortItems(StringPrimaryRecordInterface $a, StringPrimaryRecordInterface $b): int
    {
        return strnatcasecmp($a->getListLabel(), $b->getListLabel());
    }

    public function showSelectionScreen(): void
    {
        $html = $this->ui->getPage()->renderTemplate(
            UpdatersSelectionTmpl::class,
            array(
                UpdatersSelectionTmpl::KEY_UPDATERS_INSTANCE => $this
            )
        );

        echo $this->renderPage(t('%1$s maintenance', $this->driver->getAppNameShort()), $html);
    }

    public function isEnabled(UpdaterInterface $updater): bool
    {
        $versions = $updater->getValidVersions();
        if ($versions === '*') {
            return true;
        }

        if (!is_array($versions)) {
            $versions = array($versions);
        }

        return in_array($this->driver->getVersion(), $versions, true);
    }

    /**
     * @param string|StringableInterface $title
     * @param string|StringableInterface $content
     * @return string
     */
    public function renderPage(string|StringableInterface $title, string|StringableInterface $content): string
    {
        return $this->ui->createTemplate(UpdatersScaffoldTmpl::class)
            ->setVar(UpdatersScaffoldTmpl::VAR_ACTIVE_UPDATER, $this->activeUpdater)
            ->setVar(UpdatersScaffoldTmpl::VAR_TITLE, $title)
            ->setVar(UpdatersScaffoldTmpl::VAR_CONTENT, $content)
            ->render();
    }

    /**
     * @param array<string,string|int|float|bool|StringableInterface|NULL> $params
     * @return string
     */
    public function buildURL(array $params = array()): string
    {
        return Application_Request::getInstance()
            ->buildURL($params, Application_Bootstrap_Screen_Updaters::DISPATCHER_NAME);
    }

// region: Class loading

    protected function createItemInstance(string $class): ?StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            UpdaterInterface::class,
            new $class($this)
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return UpdaterInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassFolders(): array
    {
        return $this->classFolders;
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }

// endregion
}
