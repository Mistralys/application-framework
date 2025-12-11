<?php
/**
 * @package Application
 * @subpackage Development Tooling
 */

declare(strict_types=1);

namespace UI\CSSGenerator;

use Application\AppFactory;
use Application\Development\Admin\Screens\DevelArea;
use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use UI;
use UI\Admin\Screens\CSSGenDevelMode;

/**
 * CSS Generator: Detects all CSS template files in the framework and driver
 * theme CSS folders, and compiles them into production CSS files.
 *
 * @package Application
 * @subpackage Development Tooling
 *
 * @method CSSGenFile[] getAll()
 * @method CSSGenFile getByID(string $id)
 */
class CSSGen extends BaseStringPrimaryCollection
{
    public const string LOCATION_FRAMEWORK = 'default';
    public const string LOCATION_DRIVER = 'driver';
    public const string CSS_TEMPLATE_EXTENSION = 'csst';
    public const string FOLDER_PROPERTY_BASE_FOLDER = 'baseFolder';

    private static ?CSSGen $instance = null;

    /**
     * @var array<string,CSSGenLocation>
     */
    private array $locations = array();

    public function __construct()
    {
        $this->registerLocations();
    }

    public static function create() : self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return '';
    }

    public function getAdminGenerateURL(array $params=array()) : string
    {
        $params[CSSGenDevelMode::REQUEST_PARAM_GENERATE_ALL] = 'yes';

        return $this->getAdminURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = DevelArea::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = CSSGenDevelMode::URL_NAME;

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    private function registerLocations() : void
    {
        $theme = UI::getInstance()->getTheme();

        $this->registerLocation(
            self::LOCATION_FRAMEWORK,
            t('Framework base theme'),
            $theme->getDefaultPath(),
            $theme->getDefaultStylesheetsPath()
        );

        $this->registerLocation(
            self::LOCATION_DRIVER,
            t('Application theme'),
            $theme->getDriverPath(),
            $theme->getDriverStylesheetsPath()
        );

        uasort($this->locations, static function(CSSGenLocation $a, CSSGenLocation $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
    }

    private function registerLocation(string $id, string $label, string $baseFolder, string $cssFolder) : void
    {
        $this->locations[$id] = new CSSGenLocation(
            $id,
            $label,
            FolderInfo::factory($baseFolder),
            FolderInfo::factory($cssFolder)
        );
    }

    public function generateAll() : self
    {
        $files = $this->getAll();

        foreach($files as $file) {
            $file->generate();
        }

        return $this;
    }

    /**
     * @return CSSGenLocation[]
     */
    public function getLocations() : array
    {
        return array_values($this->locations);
    }

    protected function registerItems(): void
    {
        $folders = $this->getLocations();

        foreach($folders as $location) {
            $this->registerLocationItems($location);
        }
    }

    private function registerLocationItems(CSSGenLocation $location) : void
    {
        $files = FileHelper::createFileFinder($location->getCSSFolder())
            ->includeExtension(self::CSS_TEMPLATE_EXTENSION)
            ->makeRecursive()
            ->getAll();

        foreach($files as $file) {
            $this->registerItem(new CSSGenFile(
                FileInfo::factory($file),
                $location
            ));
        }
    }
}
