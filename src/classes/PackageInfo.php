<?php
/**
 * @package Application
 */

declare(strict_types=1);

use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\JSONFile;

/**
 * Utility class to retrieve information on the application
 * framework package.
 *
 * @package Application
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
final class PackageInfo
{
    public const string PROJECT_SLUG = 'mistralys/application_framework';
    public const string GITHUB_URL = 'https://github.com/Mistralys/application-framework';
    public const string PROJECT_NAME = 'Application Admin UI Framework';
    public const string PROJECT_NAME_SHORT = 'AppFramework';

    /**
     * @var ArrayDataCollection|NULL
     */
    private static ?ArrayDataCollection $composerConfig = null;

    /**
     * Gets the ID/name of the framework composer package,
     * in the form "vendor/project-name".
     * 
     * @return string
     */
    public static function getComposerID() : string
    {
        return self::PROJECT_SLUG;
    }

    public static function getGithubURL() : string
    {
        return self::GITHUB_URL;
    }

    public static function getName() : string
    {
        return self::PROJECT_NAME;
    }

    public static function getNameShort() : string
    {
        return self::PROJECT_NAME_SHORT;
    }

    /**
     * Gets the human-readable label/title of the
     * application framework project.
     *
     * @return string
     */
    public static function getDescription() : string
    {
        return self::getComposerConfig()->getString('description');
    }

    public static function getComposerFile() : JSONFile
    {
        return JSONFile::factory(__DIR__.'/../../composer.json');
    }

    public static function getComposerConfig() : ArrayDataCollection
    {
        if(self::$composerConfig !== null)
        {
            return self::$composerConfig;
        }

        $file = self::getComposerFile();
        self::$composerConfig = ArrayDataCollection::create();

        if($file->exists())
        {
            self::$composerConfig->setKeys($file->parse());
        }

        return self::$composerConfig;
    }
}
