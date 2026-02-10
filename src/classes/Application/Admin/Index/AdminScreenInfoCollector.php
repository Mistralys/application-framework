<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\Framework\AppFolder;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\AllowableMigrationInterface;
use AdminException;
use AppUtils\ClassHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\PHPFile;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

class AdminScreenInfoCollector implements StringPrimaryRecordInterface
{
    private AdminScreenInterface $screen;
    private PHPFile $classPath;
    private FolderInfo $subscreensPath;
    private string $class;
    private AdminScreenInfoCollector $parentScreen;
    private string $id;

    public function __construct(AdminScreenInterface $screen)
    {
        $this->screen = $screen;
        $this->class = get_class($screen);
        $this->id = md5($this->class);
        $this->classPath = $this->findSourceFile();
        $this->subscreensPath = FolderInfo::factory($this->classPath->getFolder().'/'.getClassTypeName($screen));
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getScreen() : AdminScreenInterface
    {
        return $this->screen;
    }

    /**
     * @return class-string<AdminScreenInterface>
     */
    public function getClass() : string
    {
        return $this->class;
    }

    private function findSourceFile() : PHPFile
    {
        $file = ClassHelper::getClassSourceFile(get_class($this->screen));
        if($file !== null) {
            return $file;
        }

        throw new AdminException(
            'Could not find screen source file.',
            sprintf(
                'Could not find source file for class [%s].',
                get_class($this->screen)
            ),
            AdminException::ERROR_SCREEN_SOURCE_NOT_FOUND
        );
    }

    public function getClassPath() : PHPFile
    {
        return $this->classPath;
    }

    public function getFolder() : FolderInfo
    {
        return $this->getClassPath()->getFolder();
    }

    public function getSubscreensFolder() : FolderInfo
    {
        return $this->subscreensPath;
    }

    public function detectParentScreenClass() : ?string
    {
        if($this->screen instanceof ClassLoaderScreenInterface) {
            return $this->screen->getParentScreenClass();
        }

        return null;
    }

    /**
     * @var AdminScreenInfoCollector[]
     */
    private array $subscreens = array();

    public function registerSubscreen(AdminScreenInfoCollector $info) : self
    {
        $this->subscreens[] = $info;
        $info->registerParentScreen($this);
        return $this;
    }

    public function registerParentScreen(AdminScreenInfoCollector $parent) : self
    {
        $this->parentScreen = $parent;
        return $this;
    }

    public function getURLName() : string
    {
        return $this->screen->getURLName();
    }

    public function getURLPath() : string
    {
        if(isset($this->parentScreen)) {
            return $this->parentScreen->getURLPath().'.'.$this->screen->getURLName();
        }

        return $this->screen->getURLName();
    }

    /**
     * @return class-string<AdminScreenInterface>[]
     */
    public function getSubscreenClasses() : array
    {
        $result = array();

        foreach($this->subscreens as $subscreen) {
            $result[] = $subscreen->getClass();
        }

        sort($result);

        return $result;
    }

    public function toArray() : array
    {
        $array = array(
            ScreenDataInterface::KEY_SCREEN_ID => $this->screen->getID(),
            ScreenDataInterface::KEY_SCREEN_URL_NAME => $this->screen->getURLName(),
            ScreenDataInterface::KEY_SCREEN_URL_PATH => $this->getURLPath(),
            ScreenDataInterface::KEY_SCREEN_TITLE => $this->screen->getTitle(),
            ScreenDataInterface::KEY_SCREEN_NAVIGATION_TITLE => $this->screen->getNavigationTitle(),
            ScreenDataInterface::KEY_SCREEN_REQUIRED_RIGHT => null,
            ScreenDataInterface::KEY_SCREEN_FEATURE_RIGHTS => null,
            ScreenDataInterface::KEY_SCREEN_CLASS => $this->getClass(),
            ScreenDataInterface::KEY_SCREEN_PATH => AppFolder::create($this->getFolder())->getIdentifier(),
            ScreenDataInterface::KEY_SCREEN_SUBSCREEN_CLASSES => $this->getSubscreenClasses()
        );

        if($this->screen instanceof AllowableMigrationInterface)
        {
            $array[ScreenDataInterface::KEY_SCREEN_REQUIRED_RIGHT] = $this->screen->getRequiredRight();
            $array[ScreenDataInterface::KEY_SCREEN_FEATURE_RIGHTS] = $this->screen->getFeatureRights();

            ksort($array[ScreenDataInterface::KEY_SCREEN_FEATURE_RIGHTS]);
        }

        return $array;
    }

    public function toTreeArray() : array
    {
        $array = $this->toArray();

        $array[ScreenDataInterface::KEY_SCREEN_SUBSCREENS] = array();
        foreach($this->subscreens as $subscreen) {
            $array[ScreenDataInterface::KEY_SCREEN_SUBSCREENS] = array_merge($array[ScreenDataInterface::KEY_SCREEN_SUBSCREENS], $subscreen->toTreeArray());
        }

        return array(
            $this->getURLName() => $array
        );
    }
}
