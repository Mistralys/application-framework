<?php
/**
 * @package Source Folders
 */

declare(strict_types=1);

namespace Application\SourceFolders;

use application\assets\classes\TestDriver\Environments\EnvironmentsConfig;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Utility class used to manage the dynamic class locations:
 * Maintains a list of folders from which source classes are
 * loaded, and offers methods to register additional folders.
 *
 * The following folders are registered by default within
 * the driver's classes folder, to automatically load source
 * classes:
 *
 * - `AjaxMethods`
 * - `API`
 * - `Area`
 * - `DeploymentTasks`
 * - `FormElements`
 * - `HealthMonitor`
 * - `LookupItems`
 * - `OfflineEvents`
 * - `UI`
 * - `Updaters`
 *
 * ## Adding additional source folders
 *
 * See {@see EnvironmentsConfig::_registerClassSourceFolders()} for
 * the typical place to register additional source folders. Use the
 * method {@see self::choose()} to access all known dynamic class
 * locations, and then use {@see BaseSourceFolder::addFolder()} to
 * register additional folders.
 *
 * @package Source Folders
 * @method BaseSourceFolder getByID(string $id)
 * @method BaseSourceFolder[] getAll()
 */
class SourceFoldersManager extends BaseClassLoaderCollection
{
    private FolderInfo $folder;
    private KnownSources $knownSources;

    private function __construct()
    {
        $this->folder = FolderInfo::factory(__DIR__.'/Sources');
        $this->knownSources = new KnownSources($this);
    }

    private static ?SourceFoldersManager $instance = null;

    public static function getInstance() : SourceFoldersManager
    {
        if (self::$instance === null) {
            self::$instance = new SourceFoldersManager();
        }

        return self::$instance;
    }

    public function choose() : KnownSources
    {
        return $this->knownSources;
    }

    /**
     * @param class-string<BaseSourceFolder> $class
     * @return StringPrimaryRecordInterface|null
     */
    protected function createItemInstance(string $class): ?StringPrimaryRecordInterface
    {
        return new $class();
    }

    /**
     * @return class-string<BaseSourceFolder>
     */
    public function getInstanceOfClassName(): string
    {
        return BaseSourceFolder::class;
    }

    public function isRecursive(): bool
    {
        return false;
    }

    public function getClassesFolder(): FolderInfo
    {
        return $this->folder;
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
