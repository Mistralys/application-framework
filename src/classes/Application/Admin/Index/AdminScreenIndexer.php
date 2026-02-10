<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface;
use Application\Interfaces\Admin\AdminActionInterface;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface;
use Application_Admin_Wizard_Step;
use Application_Driver;
use AppLocalize\Localization;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use Mistralys\AppFramework\AppFramework;
use ReflectionClass;

/**
 * @method AdminScreenInfoCollector[] getAll()
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
    private int $countContentScreens;

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
        $this->countContentScreens = 0;
        foreach($this->getAll() as $item) {
            $this->infos[] = $item;

            if($item->getScreen() instanceof AdminAreaInterface) {
                $this->roots[] = $item;
            }

            // Only screens without subscreens actually generate
            // content.
            if(!$item->getScreen()->hasSubscreens()) {
                $this->countContentScreens++;
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

    public function countContentScreens() : int
    {
        return $this->countContentScreens;
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
            ScreenDataInterface::KEY_ROOT_URL_PATHS => $paths,
            ScreenDataInterface::KEY_ROOT_FLAT => $flat,
            ScreenDataInterface::KEY_ROOT_TREE => $tree
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

    protected function createItemInstance(string $class): ?AdminScreenInfoCollector
    {
        $reflect = new ReflectionClass($class);
        if(
            // Wizard steps are not part of the sitemap
            is_a($class, Application_Admin_Wizard_Step::class, true)
            ||
            // Stubs used for testing purposes only
            is_a($class, AdminScreenStubInterface::class, true)
            ||
            $reflect->isAbstract()
            ||
            $reflect->isInterface()
        ) {
            return null;
        }

        if(is_a($class, AdminAreaInterface::class, true)) {
            $screen = ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, false)
            );
        } else if(is_a($class, AdminModeInterface::class, true)) {
            $screen = ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubArea)
            );
        } else if(is_a($class, AdminSubmodeInterface::class, true)) {
            $screen = ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubMode)
            );
        } else if(is_a($class, AdminActionInterface::class, true)) {
            $screen = ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubSubmode)
            );
        } else {
            // Fall back to the skeleton constructor
            $screen = ClassHelper::requireObjectInstanceOf(
                AdminScreenInterface::class,
                new $class($this->driver, $this->stubArea)
            );
        }

        return new AdminScreenInfoCollector($screen);
    }

    public function getInstanceOfClassName(): string
    {
        return AdminScreenInterface::class;
    }

    public function getClassFolders(): array
    {
        return AppFramework::getInstance()->getClassFolders();
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
