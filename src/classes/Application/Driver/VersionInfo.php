<?php
/**
 * @package Application
 * @subpackage Driver
 */

declare(strict_types=1);

namespace Application\Driver;

use Application\AppFactory;
use Application\DeploymentRegistry\Tasks\StoreCurrentVersionTask;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\FileHelper\FileInfo;
use Mistralys\VersionParser\VersionParser;

/**
 * Utility class used to get information on the application's version.
 *
 * > NOTE: The version is stored automatically when the application
 * > is deployed with the {@see StoreCurrentVersionTask} deployment
 * > task.
 *
 * ## Usage
 *
 * Use the AppFactory to get an instance of the class:
 * {@see AppFactory::createVersionInfo()}.
 *
 * @package Application
 * @subpackage Driver
 * @see AppFactory::createVersionInfo()
 * @see StoreCurrentVersionTask
 */
class VersionInfo implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const FILE_NAME = 'version';

    private static ?self $instance = null;
    private string $logIdentifier;

    public static function getInstance() : self
    {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->logIdentifier = 'VersionInfo';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    private ?FileInfo $versionFile = null;

    /**
     * Gets the path to the version file.
     * @return FileInfo
     */
    public function getVersionFile() : FileInfo
    {
        if(!isset($this->versionFile)) {
            $this->versionFile = FileInfo::factory(APP_ROOT . '/'. self::FILE_NAME);
        }

        return $this->versionFile;
    }

    /**
     * Stores the current version extracted from the changelog
     * into the version file.
     *
     * @return $this
     */
    public function storeCurrentVersion() : self
    {
        $version = AppFactory::createDevChangelog()->getCurrentVersion()->getTagVersion();

        $this->log(
            'Storing the current version [%s] in the [%s] file.',
            $version,
            self::FILE_NAME
        );

        $this->getVersionFile()->putContents($version);
        return $this;
    }

    private ?string $version = null;

    /**
     * Retrieves the full application version string, e.g. "3.2.6-Beta1".
     * @return string
     * @cached
     */
    public function getFullVersion() : string
    {
        if(isset($this->version)) {
            return $this->version;
        }

        $file = $this->getVersionFile();

        // Fallback for when the version file has not been created
        // by the deployment task.
        if(!$file->exists()) {
            $this->log('LOAD | FALLBACK: Version file does not exist, storing version.');
            $this->storeCurrentVersion();
        }

        $this->version = $file->getContents();

        $this->log('LOAD | Detected version: %s', $this->version);

        return $this->version;
    }

    private ?VersionParser $versionInfo = null;

    /**
     * Gets the version parser instance for the current version,
     * to access extended information on the version.
     *
     * @return VersionParser
     * @cached
     */
    public function getParser() : VersionParser
    {
        if(!isset($this->versionInfo)) {
            $this->versionInfo = VersionParser::create($this->getFullVersion());
        }

        return $this->versionInfo;
    }

    /**
     * Deletes the version file and clears all internal caches.
     */
    public function clearVersion() : self
    {
        $this->log('Deleting the [%s] file.', self::FILE_NAME);

        $this->getVersionFile()->delete();

        $this->version = null;
        $this->versionFile = null;
        $this->versionInfo = null;

        return $this;
    }

    /**
     * @return bool Whether the version file exists.
     */
    public function fileExists() : bool
    {
        return $this->getVersionFile()->exists();
    }
}
