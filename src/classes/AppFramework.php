<?php

declare(strict_types=1);

namespace Mistralys\AppFramework;

use AppUtils\FileHelper;
use Mistralys\VersionParser\VersionParser;

class AppFramework
{
    private const GITHUB_URL = 'https://github.com/Mistralys/application-framework.git';

    private static ?AppFramework $instance = null;
    private string $installFolder;
    private ?VersionParser $version = null;

    private function __construct()
    {
        $this->installFolder = __DIR__.'/../../';
    }

    public function getName() : string
    {
        return 'AppFramework';
    }

    public function getInstallFolder(): string
    {
        return $this->installFolder;
    }

    public function getGithubURL() : string
    {
        return self::GITHUB_URL;
    }

    public function getVersion() : VersionParser
    {
        if(!isset($this->version)) {
            $this->version = VersionParser::create(FileHelper::readContents($this->getVersionPath()));
        }

        return $this->version;
    }

    public function getVersionPath() : string
    {
        return __DIR__.'/../../VERSION';
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
