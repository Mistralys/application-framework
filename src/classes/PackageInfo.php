<?php
/**
 * File containing the class {@see PackageInfo}.
 *
 * @package Application
 * @see PackageInfo
 */

declare(strict_types=1);

use AppUtils\FileHelper;

/**
 * Utility class to retrieve information on the application
 * framework package.
 *
 * @package Application
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
final class PackageInfo
{
    public const GITHUB_URL = 'https://github.com/Mistralys/application-framework';
    public const PROJECT_TITLE = 'Application admin UI framework';

    /**
     * @var array<string,mixed>|NULL
     */
    private static ?array $composerConfig = null;

    /**
     * Gets the ID/name of the framework composer package,
     * in the form "vendor/project-name".
     * 
     * @return string
     */
    public static function getComposerID() : string
    {
        $info = self::getComposerConfig();
        return $info['name'];
    }

    /**
     * Gets the human-readable label/title of the
     * application framework project.
     *
     * @return string
     */
    public static function getProjectLabel() : string
    {
        $info = self::getComposerConfig();
        return $info['description'];
    }

    public static function getComposerConfig() : array
    {
        if(self::$composerConfig !== null)
        {
            return self::$composerConfig;
        }

        $config = FileHelper::parseJSONFile(__DIR__.'/../../composer.json');
        self::$composerConfig = $config;

        return $config;
    }
}
