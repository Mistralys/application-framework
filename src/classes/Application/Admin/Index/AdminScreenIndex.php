<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application;
use Application\Interfaces\Admin\AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use AdminException;
use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\PHPFile;
use UI\AdminURLs\AdminURLInterface;

class AdminScreenIndex
{
    /**
     * @var array<string,class-string<AdminURLInterface>>
     */
    private array $paths;

    /**
     * @var array<class-string<AdminURLInterface>,array<int|string,mixed>>
     */
    private array $flat;

    /**
     * @var array<int,array<int|string,mixed>>
     */
    private array $tree;

    private static ?AdminScreenIndex $instance = null;

    public static function getInstance() : AdminScreenIndex
    {
        if(!isset(self::$instance)) {
            self::$instance = new AdminScreenIndex();
        }

        return self::$instance;
    }

    public static function getAPIMethodsFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/API/Methods')->requireExists();
    }

    private function __construct()
    {
        $file = self::getIndexFile();

        if(!$file->exists()) {
            throw new AdminException(
                'Admin screen index file not found.',
                sprintf(
                    'The admin screen index file was not found at expected location [%s].',
                    $file->getPath()
                ),
                AdminException::ERROR_SCREEN_INDEX_NOT_FOUND
            );
        }

        $info = include $file->getPath();

        if(!is_array($info) || !isset($info[ScreenDataInterface::KEY_ROOT_URL_PATHS], $info[ScreenDataInterface::KEY_ROOT_FLAT], $info[ScreenDataInterface::KEY_ROOT_TREE])) {
            throw new AdminException(
                'Admin screen index file is invalid.',
                sprintf(
                    'The admin screen index file at [%s] did not return a valid array.',
                    $file->getPath()
                ),
                AdminException::ERROR_SCREEN_INDEX_INVALID
            );
        }

        $data = ArrayDataCollection::create($info);

        $this->paths = $data->getArrayFlavored(ScreenDataInterface::KEY_ROOT_URL_PATHS)->filterAssocString();
        $this->flat = $data->getArray(ScreenDataInterface::KEY_ROOT_FLAT);
        $this->tree = $data->getArray(ScreenDataInterface::KEY_ROOT_TREE);
    }

    private static ?PHPFile $indexFile = null;

    public static function getIndexFile() : PHPFile
    {
        if(!isset(self::$indexFile)) {
            self::$indexFile = PHPFile::factory(Application::getStorageSubfolderPath('admin').'/sitemap.php');
        }

        return self::$indexFile;
    }

    public function urlPathExists(string $path) : bool
    {
        return isset($this->paths[$path]);
    }

    public function getClassByURLPath(string $path) : ?string
    {
        return $this->paths[$path] ?? null;
    }

    /**
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
     * @return array<string,string> Screen ID => URL Name pairs
     */
    public function getSubscreenIDNames(AdminScreenInterface|string $subject) : array
    {
        $result = array();
        foreach($this->getSubscreenClasses($subject) as $class) {
            $id = $this->flat[$class][ScreenDataInterface::KEY_SCREEN_ID];
            $result[$id] = $this->flat[$class][ScreenDataInterface::KEY_SCREEN_URL_NAME];
        }

        return $result;
    }

    /**
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
     * @return class-string<AdminScreenInterface>[]
     */
    public function getSubscreenClasses(AdminScreenInterface|string $subject) : array
    {
        $className = $this->subject2class($subject);

        $list = $this->flat[$className][ScreenDataInterface::KEY_SCREEN_SUBSCREEN_CLASSES] ?? null;

        if(is_array($list)) {
            return $list;
        }

        return array();
    }

    /**
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
     * @param string $idOrName
     * @return class-string<AdminScreenInterface>
     */
    public function getSubscreenClass(AdminScreenInterface|string $subject, string $idOrName) : string
    {
        foreach($this->getSubscreenClasses($subject) as $class) {
            if($idOrName === $this->flat[$class][ScreenDataInterface::KEY_SCREEN_ID]) {
                return $class;
            }

            if($idOrName === $this->flat[$class][ScreenDataInterface::KEY_SCREEN_URL_NAME]) {
                return $class;
            }
        }

        throw new AdminException(
            'Subscreen not found.',
            sprintf(
                'The subscreen with ID or URL name [%s] was not found for the screen/class [%s].',
                $idOrName,
                $this->subject2class($subject)
            ),
            AdminException::ERROR_SCREEN_SUBSCREEN_NOT_FOUND
        );
    }

    /**
     * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
     * @return class-string<AdminScreenInterface>
     */
    private function subject2class(AdminScreenInterface|string $subject) : string
    {
        if($subject instanceof AdminScreenInterface) {
            return get_class($subject);
        }

        return $subject;
    }

    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/Screens')->requireExists();
    }

    public function getTree() : array
    {
        return $this->tree;
    }

    public function countScreens() : int
    {
        return count($this->flat);
    }

    /**
     * @return array<string,class-string<AdminAreaInterface>>
     */
    public function getAdminAreas() : array
    {
        $areas = array();
        foreach($this->tree as $area) {
            $areas[$area[ScreenDataInterface::KEY_SCREEN_URL_NAME]] = $area[ScreenDataInterface::KEY_SCREEN_CLASS];
        }

        return $areas;
    }
}
