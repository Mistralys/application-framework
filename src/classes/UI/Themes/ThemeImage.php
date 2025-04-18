<?php

declare(strict_types=1);

namespace UI\Themes;

use Application\AppFactory;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use UI_Themes_Exception;
use function AppUtils\parseVariable;

class ThemeImage
{
    private string $relativePath;
    private ?FileInfo $file = null;
    private ?string $url = null;

    /**
     * @var array<string, ThemeImage>
     */
    private static array $instances = array();

    public function __construct(string $relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * @param string|ThemeImage|mixed $filePath
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
            $this->file = FileInfo::factory(AppFactory::createDriver()->getTheme()->getTemplatePath($this->relativePath));
        }

        return $this->file;
    }

    public function getURL() : string
    {
        if(!isset($this->url)) {
            $this->url = AppFactory::createDriver()->getTheme()->getTemplateURL($this->relativePath);
        }

        return $this->url;
    }
}
