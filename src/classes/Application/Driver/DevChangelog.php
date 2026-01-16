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
use Mistralys\VersionParser\VersionParser;

/**
 * Utility class for handling the developer changelog file.
 * If the file {@see self::CHANGELOG_FILE} is present, information
 * can be loaded from it.
 *
 * @package Application
 * @subpackage Driver
 */
class DevChangelog implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string CHANGELOG_FILE = 'dev-changelog.md';
    public const int ERROR_NO_CHANGELOG_FILE_FOUND = 164901;
    public const string DEFAULT_VERSION_STRING = '0.0.0';

    private FileInfo $changelogFile;
    private ?ChangelogParser $parser = null;
    private ?VersionParser $currentVersion = null;
    private string $logIdentifier;

    public function __construct()
    {
        $this->changelogFile = FileInfo::factory(APP_ROOT.'/'.self::CHANGELOG_FILE);
        $this->logIdentifier = 'DevChangeLog';
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    /**
     * Deletes the version file if it exists. Has no effect otherwise.
     *
     * @return $this
     */
    public function clearCurrentVersion() : self
    {
        $this->currentVersion = null;
        return $this;
    }

    public function changelogExists() : bool
    {
        return $this->changelogFile->exists();
    }

    /**
     * Gets the current version of the application.
     *
     * This is determined by the latest version found in the developer
     * changelog file, {@see self::CHANGELOG_FILE}.
     *
     * > NOTE: If the changelog file does not exist or does not contain any
     * > version information, the version is assumed to be {@see self::DEFAULT_VERSION_STRING}.
     *
     * @return VersionParser
     * @cached
     */
    public function getCurrentVersion() : VersionParser
    {
        if(isset($this->currentVersion)) {
            return $this->currentVersion;
        }

        $this->currentVersion = $this->resolveCurrentVersion();

        $this->log('LOAD | Detected version: %s', $this->currentVersion->getVersion());

        return $this->currentVersion;
    }

    private function resolveCurrentVersion() : VersionParser
    {
        if(!$this->changelogFile->exists()) {
            $this->log('LOAD | No version file or changelog file found; Using default.');
            return VersionParser::create(self::DEFAULT_VERSION_STRING);
        }

        $latest = $this->getChangelog()->getLatestVersion();

        if($latest === null) {
            $this->log('LOAD | No latest version found in the changelog; Using default.');
            return VersionParser::create(self::DEFAULT_VERSION_STRING);
        }

        $this->log('LOAD | Using latest changelog version.');
        return $latest->getVersionInfo();
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
