<?php

declare(strict_types=1);

namespace Mistralys\AppFramework;

use Application\AppFactory;
use Application\Exception\ApplicationException;
use AppUtils\BaseException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;
use Mistralys\ChangelogParser\ChangelogParser;
use Mistralys\VersionParser\VersionParser;

class AppFramework
{
    private const string GITHUB_URL = 'https://github.com/Mistralys/application-framework.git';

    public const int ERROR_CANNOT_DETERMINE_VERSION = 162401;

    private static ?AppFramework $instance = null;
    private string $installFolder;
    private ?VersionParser $version = null;

    private function __construct()
    {
        $this->installFolder = __DIR__.'/../../../../';
    }

    public function getName() : string
    {
        return 'AppFramework';
    }

    public function getInstallFolder(): string
    {
        return $this->installFolder;
    }

    public function getClassesFolder() : string
    {
        return rtrim($this->getInstallFolder(), '/').'/src/classes';
    }

    /**
     * Gets all folders from which classes can be loaded:
     * From the Framework itself and from the application.
     *
     * @return FolderInfo[]
     */
    public function getClassFolders(): array
    {
        return array(
            FolderInfo::factory($this->getClassesFolder())->requireExists(),
            FolderInfo::factory(AppFactory::createDriver()->getRootClassesFolder())->requireExists()
        );
    }

    public function getNameLinked() : string
    {
        return (string)sb()->link($this->getName(), $this->getGithubURL());
    }

    public function getGithubURL() : string
    {
        return self::GITHUB_URL;
    }

    public function getVersion() : VersionParser
    {
        if(!isset($this->version)) {
            $this->version = $this->detectVersion();
        }

        return $this->version;
    }

    /**
     * Attempts to determine the current framework version from:
     *
     * 1. The `VERSION` file, if it exists and is current.
     * 2. The `changelog.md` file
     *
     * > NOTE: The `VERSION` file is automatically created and updated as necessary.
     *
     * @return VersionParser
     * @throws BaseException
     * @throws FileHelper_Exception
     */
    private function detectVersion() : VersionParser
    {
        $versionFile = FileInfo::factory($this->getVersionPath());
        $changelogFile = FileInfo::factory($this->getChangelogPath());

        // Use the existing VERSION file if it is current to the changelog file.
        if($versionFile->exists() && $versionFile->getModifiedDate() >= $changelogFile->getModifiedDate()) {
            return VersionParser::create(trim($versionFile->getContents()));
        }

        $version = ChangelogParser::parseMarkdownFile($changelogFile)->getLatestVersion();

        if($version !== null)
        {
            // Save the version to the VERSION file to cache it
            $versionFile->putContents($version->getNumber());

            return $version->getVersionInfo();
        }

        throw new ApplicationException(
            'Could not determine framework version.',
            sprintf(
                'The version could not be determined from the changelog file at [%s].',
                $changelogFile
            ),
            self::ERROR_CANNOT_DETERMINE_VERSION
        );
    }

    public function getChangelogPath() : string
    {
        return $this->getInstallFolder().'/changelog.md';
    }

    public function getVersionPath() : string
    {
        return $this->getInstallFolder().'/VERSION';
    }

    public static function getInstance() : AppFramework
    {
        if(!isset(self::$instance))
        {
            self::$instance = new AppFramework();
        }

        return self::$instance;
    }
}
