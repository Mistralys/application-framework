<?php

declare(strict_types=1);

namespace Application\Admin\Index;

use Application;
use Application_Admin_Exception;
use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\PHPFile;
use UI\AdminURLs\AdminURLInterface;

class AdminScreenIndex
{
    public const string KEY_URL_PATHS = 'urlPaths';
    public const string KEY_FLAT = 'flat';
    public const string KEY_TREE = 'tree';

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

    private function __construct()
    {
        $file = self::getIndexFile();

        if(!$file->exists()) {
            throw new Application_Admin_Exception(
                'Admin screen index file not found.',
                sprintf(
                    'The admin screen index file was not found at expected location [%s].',
                    $file->getPath()
                ),
                Application_Admin_Exception::ERROR_SCREEN_INDEX_NOT_FOUND
            );
        }

        $info = include $file->getPath();

        if(!is_array($info) || !isset($info[self::KEY_URL_PATHS], $info[self::KEY_FLAT], $info[self::KEY_TREE])) {
            throw new Application_Admin_Exception(
                'Admin screen index file is invalid.',
                sprintf(
                    'The admin screen index file at [%s] did not return a valid array.',
                    $file->getPath()
                ),
                Application_Admin_Exception::ERROR_SCREEN_INDEX_INVALID
            );
        }

        $data = ArrayDataCollection::create($info);

        $this->paths = $data->getArrayFlavored(self::KEY_URL_PATHS)->filterAssocString();
        $this->flat = $data->getArray(self::KEY_FLAT);
        $this->tree = $data->getArray(self::KEY_TREE);
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
}
