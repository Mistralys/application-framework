<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application\Admin\ClassLoaderScreenInterface;
use Application\AppFactory;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\AllowableMigrationInterface;
use Application_Admin_Exception;
use AppUtils\ClassHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\PHPFile;

class AdminScreenInfoCollector
{
    private AdminScreenInterface $screen;
    private PHPFile $classPath;
    private FolderInfo $subscreensPath;
    private string $class;
    private AdminScreenInfoCollector $parentScreen;

    public function __construct(AdminScreenInterface $screen)
    {
        $this->screen = $screen;
        $this->class = get_class($screen);
        $this->classPath = $this->findSourceFile();
        $this->subscreensPath = FolderInfo::factory($this->classPath->getFolder().'/'.getClassTypeName($screen));
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

        throw new Application_Admin_Exception(
            'Could not find screen source file.',
            sprintf(
                'Could not find source file for class [%s].',
                get_class($this->screen)
            ),
            Application_Admin_Exception::ERROR_SCREEN_SOURCE_NOT_FOUND
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

    public function toArray() : array
    {
        $array = array(
            'id' => $this->screen->getID(),
            'urlName' => $this->screen->getURLName(),
            'urlPath' => $this->getURLPath(),
            'title' => $this->screen->getTitle(),
            'navigationTitle' => $this->screen->getNavigationTitle(),
            'requiredRight' => null,
            'featureRights' => null,
            'class' => $this->getClass(),
            'path' => FileHelper::relativizePath($this->getFolder()->getPath(), AppFactory::createDriver()->getClassesFolder()),
        );

        if($this->screen instanceof AllowableMigrationInterface)
        {
            $array['requiredRight'] = $this->screen->getRequiredRight();
            $array['featureRights'] = $this->screen->getFeatureRights();

            ksort($array['featureRights']);
        }

        return $array;
    }

    public function toTreeArray() : array
    {
        $array = $this->toArray();

        $array['subscreens'] = array();
        foreach($this->subscreens as $subscreen) {
            $array['subscreens'] = array_merge($array['subscreens'], $subscreen->toTreeArray());
        }

        return array(
            $this->getURLName() => $array
        );
    }
}
