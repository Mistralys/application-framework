<?php
/**
 * @package Source Folders
 */

declare(strict_types=1);

namespace Application\SourceFolders;

use Application\AppFactory;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Base class for source folder definitions.
 *
 * @package Source Folders
 */
abstract class BaseSourceFolder implements StringPrimaryRecordInterface
{
    private string $sourceID;
    private string $sourceLabel;

    /**
     * @var FolderInfo[]
     */
    private array $folders = array();
    private static ?FolderInfo $classesFolder = null;

    public function __construct(string $sourceID, string $sourceLabel)
    {
        $this->sourceID = $sourceID;
        $this->sourceLabel = $sourceLabel;

        if(!isset(self::$classesFolder)) {
            self::$classesFolder = FolderInfo::factory(AppFactory::createDriver()->getClassesFolder());
        }
    }

    public function getID() : string
    {
        return $this->sourceID;
    }

    public function getLabel() : string
    {
        return $this->sourceLabel;
    }

    public function getClassesFolder() : FolderInfo
    {
        return self::$classesFolder;
    }

    /**
     * Add a folder to load classes from for this source.
     * @param FolderInfo|string $folder
     * @return $this
     */
    public function addFolder(FolderInfo|string $folder) : self
    {
        if(is_string($folder)) {
            $folder = FolderInfo::factory($folder);
        }

        $this->folders[] = $folder;
        return $this;
    }

    /**
     * Get the list of folders to load classes from for this source.
     *
     * > NOTE: Only existing folders are returned.
     * > Use {@see getRegisteredFolders()} to get all registered folders.
     *
     * @return FolderInfo[]
     */
    public function resolveFolders() : array
    {
        return array_filter($this->folders, static function(FolderInfo $folder) : bool {
            return $folder->exists();
        });
    }

    /**
     * Gets all folders that have been registered for this source.
     *
     * > NOTE: This can include folders that do not exist.
     *
     * @return FolderInfo[]
     */
    public function getRegisteredFolders() : array
    {
        return $this->folders;
    }
}
