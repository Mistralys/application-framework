<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application;
use Application\Admin\Area\Mode\BaseSubmode;
use Application\Admin\Area\Mode\Submode\BaseAction;
use Application\Admin\BaseArea;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Admin_Area;
use Application_Admin_Wizard_Step;
use Application_Driver;
use AppLocalize\Localization;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use AppUtils\FileHelper\FolderInfo;
use ReflectionClass;

/**
 * @method AdminScreenInterface[] getAll()
 */
class AdminScreenIndexer extends BaseClassLoaderCollectionMulti
{
    private Application_Driver $driver;

    /**
     * @var AdminScreenInfoCollector[]
     */
    private array $infos = array();

    /**
     * @var AdminScreenInfoCollector[]
     */
    private array $roots;
    private StubArea $stubArea;
    private StubMode $stubMode;
    private StubSubmode $stubSubmode;
    private bool $indexed = false;

    public function __construct(Application_Driver $driver)
    {
        $this->driver = $driver;
    }

    public function index() : self
    {
        if($this->indexed) {
            return $this;
        }

        $this->indexed = true;

        Localization::selectAppLocale(Localization\Locale\en_GB::LOCALE_NAME);

        $this->stubArea = new StubArea($this->driver, false);
        $this->stubMode = new StubMode($this->driver, $this->stubArea);
        $this->stubSubmode = new StubSubmode($this->driver, $this->stubMode);

        $this->infos = array();
        $this->roots = array();
        foreach($this->getAll() as $item) {
            $info = new AdminScreenInfoCollector($item);
            $this->infos[] = $info;

            if($info->getScreen() instanceof Application_Admin_Area) {
                $this->roots[] = $info;
            }
        }

        $this->buildTree();

        AdminScreenIndex::getIndexFile()->putStatements('return '.var_export($this->serialize(), true).';');

        return $this;
    }

    public function countScreens() : int
    {
        return count($this->infos);
    }

    public function serialize() : array
    {
        $this->index();

        $tree = array();
        foreach($this->roots as $root) {
            $tree = array_merge($tree, $root->toTreeArray());
        }

        $paths = array();
        $flat = array();
        foreach($this->infos as $info) {
            $flat[$info->getClass()] = $info->toArray();
            $paths[$info->getURLPath()] = $info->getClass();
        }

        ksort($flat);
        ksort($paths);

        return array(
            AdminScreenIndex::KEY_URL_PATHS => $paths,
            AdminScreenIndex::KEY_FLAT => $flat,
            AdminScreenIndex::KEY_TREE => $tree
        );
    }

    private function buildTree() : void
    {
        foreach($this->infos as $subject) {
            foreach ($this->infos as $info) {
                if ($info->detectParentScreenClass() === $subject->getClass()) {
                    $subject->registerSubscreen($info);
                    continue;
                }

                if($info->getFolder() === $subject->getSubscreensFolder()) {
                    $subject->registerSubscreen($info);
                }
            }
        }
    }

    protected function createItemInstance(string $class): ?AdminScreenInterface
    {
        $reflect = new ReflectionClass($class);
        if(
            // Wizard steps are not part of the sitemap
            is_a($class, Application_Admin_Wizard_Step::class, true)
            ||
            $reflect->isAbstract()
            ||
            $reflect->isInterface()
        ) {
            return null;
        }

        if(is_a($class, Application_Admin_Area::class, true) || is_a($class, BaseArea::class, true)) {
            return ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, false)
            );
        }

        if(is_a($class, \Application_Admin_Area_Mode::class, true)) {
            return ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubArea)
            );
        }

        if(is_a($class, \Application_Admin_Area_Mode_Submode::class, true) || is_a($class, BaseSubmode::class, true)) {
            return ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubMode)
            );
        }

        if(is_a($class, \Application_Admin_Area_Mode_Submode_Action::class, true) || is_a($class, BaseAction::class, true)) {
            return ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubSubmode)
            );
        }

        // Fall back to the skeleton constructor
        return ClassHelper::requireObjectInstanceOf(
            AdminScreenInterface::class,
            new $class($this->driver, $this->stubArea)
        );
    }

    public function getInstanceOfClassName(): string
    {
        return AdminScreenInterface::class;
    }

    public function getClassFolders(): array
    {
        return array(
            FolderInfo::factory($this->driver->getClassesFolder().'/Area')
        );
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
