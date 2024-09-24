<?php
/**
 * @package Application
 * @subpackage Driver
 */

declare(strict_types=1);

namespace Application\Driver;

use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;
use Mistralys\ChangelogParser\ChangelogParser;
use Mistralys\ChangelogParser\ChangelogParserException;
use Mistralys\VersionParser\VersionParser;

/**
 * Utility class for handling the developer changelog file.
 * If the file {@see self::CHANGELOG_FILE} is present, information
 * can be loaded from it, and the {@see self::VERSION_FILE} can be
 * generated from it automatically.
 *
 * @package Application
 * @subpackage Driver
 */
class DevChangelog implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const CHANGELOG_FILE = 'dev-changelog.md';
    public const VERSION_FILE = 'version';
    public const ERROR_NO_CHANGELOG_FILE_FOUND = 164901;

    private FileInfo $changelogFile;
    private ?ChangelogParser $parser = null;
    private ?VersionParser $currentVersion = null;
    private FileInfo $versionFile;
    private string $logIdentifier;

    public function __construct()
    {
        $this->versionFile = FileInfo::factory(APP_ROOT.'/'.self::VERSION_FILE);
        $this->changelogFile = FileInfo::factory(APP_ROOT.'/'.self::CHANGELOG_FILE);
        $this->logIdentifier = 'DevChangeLog';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * Creates or updates the {@see self::VERSION_FILE} with the latest version
     * found in the developer changelog.
     *
     * @return $this
     *
     * @throws DriverException
     * @throws FileHelper_Exception
     * @throws ChangelogParserException
     */
    public function writeVersionFile() : self
    {
        $this->log('Writing the version to disk.', self::VERSION_FILE);

        $this->versionFile->putContents(
            $this->getChangelog()
                ->requireLatestVersion()
                ->getVersionInfo()
                ->getVersion()
        );

        return $this;
    }

    /**
     * Deletes the version file if it exists. Has no effect otherwise.
     *
     * @return $this
     * @throws FileHelper_Exception
     */
    public function clearCurrentVersion() : self
    {
        $this->log('Deleting the [%s] file.', self::VERSION_FILE);

        $this->versionFile->delete();
        $this->currentVersion = null;
        return $this;
    }

    public function changelogExists() : bool
    {
        return $this->changelogFile->exists();
    }

    public function currentVersionExists() : bool
    {
        return $this->versionFile->exists();
    }

    /**
     * Gets the current version of the application.
     *
     * This is determined by the {@see self::VERSION_FILE} if it exists,
     * or by the latest version found in the developer changelog. If the
     * version file does not exist but the changelog file exists, the
     * version file is created automatically.
     *
     * NOTE: If neither the version file nor the changelog file exist, the
     * version is assumed to be `0.0.0`.
     *
     * @return VersionParser
     * @throws ChangelogParserException
     * @throws DriverException
     * @throws FileHelper_Exception
     */
    public function getCurrentVersion() : VersionParser
    {
        if(isset($this->currentVersion)) {
            return $this->currentVersion;
        }

        $this->currentVersion = $this->resolveCurrentVersion();

        $this->log('LOAD | Detected version: %s', $this->currentVersion->getVersion());

        if(!$this->versionFile->exists() && isset($this->parser)) {
            // We have already loaded the full changelog,
            // so we might as well use the occasion to
            // write the version file.
            $this->writeVersionFile();
        }

        return $this->currentVersion;
    }

    private function resolveCurrentVersion() : VersionParser
    {
        if($this->versionFile->exists()) {
            $this->log('LOAD | Version file found.');
            return VersionParser::create($this->versionFile->getContents());
        }

        if(!$this->changelogFile->exists()) {
            $this->log('LOAD | No version file or changelog file found; Using default.');
            return VersionParser::create('0.0.0');
        }

        $this->log('LOAD | Using latest changelog version.');
        return $this->getChangelog()->getLatestVersion()->getVersionInfo();
    }

    /**
     * Loads the developer changelog file and returns the
     * changelog parser instance.
     *
     * @return ChangelogParser
     * @throws DriverException
     * @throws FileHelper_Exception
     */
    public function getChangelog() : ChangelogParser
    {
        if(isset($this->parser)) {
            return $this->parser;
        }

        if(!$this->changelogFile->exists()) {
            throw new DriverException(
                'The developer changelog file does not exist.',
                sprintf(
                    'Expected the file to be at: '.PHP_EOL.
                    '%s',
                    $this->changelogFile
                ),
                self::ERROR_NO_CHANGELOG_FILE_FOUND
            );
        }

        $this->parser = ChangelogParser::parseMarkdownFile($this->changelogFile->getPath());

        return $this->parser;
    }
}
