<?php
/**
 * @package UserInterface
 * @subpackage Themes
 */

declare(strict_types=1);

namespace UI\Themes;

use Application\AppFactory;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use UI_Themes_Exception;
use UI_Themes_Theme;
use function AppUtils\parseVariable;

/**
 * Utility class to handle theme images.
 *
 * ## Usage
 *
 * 1. Create an instance via {@see self::create()}.
 *
 * @package UserInterface
 * @subpackage Themes
 */
class ThemeImage
{
    private string $relativePath;
    private ?FileInfo $file = null;
    private ?string $url = null;

    /**
     * @var array<string, ThemeImage>
     */
    private static array $instances = array();
    private UI_Themes_Theme $theme;

    public function __construct(string $relativeOrAbsolutePath)
    {
        $this->theme = AppFactory::createDriver()->getTheme();

        if(file_exists($relativeOrAbsolutePath)) {
            $this->relativePath = $this->relativizePath($relativeOrAbsolutePath);
        } else {
            $this->relativePath = $relativeOrAbsolutePath;
        }
    }

    /**
     * Converts an absolute image path to a relative one
     * starting from the theme's {@see UI_Themes_Theme::FILE_TYPE_GRAPHIC}
     * image folder.
     *
     * @param string $path
     * @return string
     * @throws UI_Themes_Exception
     */
    private function relativizePath(string $path) : string
    {
        $path = FileHelper::normalizePath($path);
        $parts = ConvertHelper::explodeTrim('themes/'.$this->theme->getID().'/'.UI_Themes_Theme::FILE_TYPE_GRAPHIC, $path);

        if(count($parts) === 2) {
            return ltrim($parts[1], '/');
        }

        throw new UI_Themes_Exception(
            'Unrecognized theme file path provided.',
            sprintf(
                'Path [%s] does not contain the theme ID [%s].',
                $path,
                $this->theme->getID()
            ),
            UI_Themes_Exception::ERROR_UNRECOGNIZED_THEME_PATH
        );
    }

    /**
     * @param string|ThemeImage|mixed $filePath Can be an absolute path, relative path, or an existing instance.
     * @return self
     * @throws UI_Themes_Exception
     */
    public static function create($filePath) : self
    {
        if($filePath instanceof self) {
            return $filePath;
        }

        if(is_string($filePath)) {
            return self::createInstance($filePath);
        }

        throw new UI_Themes_Exception(
            'Invalid theme file path provided.',
            sprintf(
                'Expected string or instance of [%s], got [%s].',
                self::class,
                parseVariable($filePath)->enableType()->toString()
            ),
            UI_Themes_Exception::ERROR_INVALID_THEME_PATH
        );
    }

    private static function createInstance(string $filePath) : self
    {
        $filePath = FileHelper::normalizePath($filePath);

        if(strpos($filePath, '../') !== false || strpos($filePath, '/..') !== false) {
            throw new UI_Themes_Exception(
                'Invalid theme file path provided.',
                sprintf(
                    'Path [%s] contains folder navigation [..]. This is not allowed.',
                    $filePath
                ),
                UI_Themes_Exception::ERROR_THEME_PATH_CONTAINS_NAVIGATION
            );
        }

        $filePath = ltrim($filePath, '/');

        if(!isset(self::$instances[$filePath])) {
            self::$instances[$filePath] = new self($filePath);
        }

        return self::$instances[$filePath];
    }

    public function getRelativePath() : string
    {
        return $this->relativePath;
    }

    public function getFile() : FileInfo
    {
        if(!isset($this->file)) {
            $this->file = FileInfo::factory($this->theme->getImagePath($this->relativePath));
        }

        return $this->file;
    }

    public function getURL() : string
    {
        if(!isset($this->url)) {
            $this->url = $this->theme->getImageURL($this->relativePath);
        }

        return $this->url;
    }

    public function getMimeType() : string
    {
        return $this->getFile()->getMimeType();
    }
}
