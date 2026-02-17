<?php
/**
 * @package Application
 * @subpackage ErrorDetails
 */

declare(strict_types=1);

namespace Application\ErrorDetails;

use Application\Application;
use Application_ErrorDetails_ThemeFile;
use Throwable;

/**
 * Specialized exception renderer for error pages, which includes support for
 * theme-based templates and assets.
 *
 * @package Application
 * @subpackage ErrorDetails
 */
class ExceptionPageRenderer extends ExceptionRenderer
{
    protected string $title;
    protected string $abstract;
    protected string $sentContent;

    public function __construct(string $title, string $abstract, ?string $sentContent, Throwable $e, bool $develinfo = false)
    {
        $this->title = $title;
        $this->abstract = $abstract;
        $this->sentContent = (string)$sentContent;

        parent::__construct($e, $develinfo);
    }

    private ?string $templateFile = null;

    private ?ThemeLocation $themeLocation = null;
    private bool $locationsChecked = false;

    public function getTemplateFile(): ?string
    {
        $this->detectLocations();

        return $this->templateFile;
    }

    public function getThemeLocation(): ?ThemeLocation
    {
        $this->detectLocations();

        return $this->themeLocation;
    }

    public function detectLocations(): void
    {
        if ($this->locationsChecked) {
            return;
        }

        $this->locationsChecked = true;

        $contentType = $this->getContentType();

        foreach ($this->getThemeLocations() as $location) {
            $file = $location->getPath() . '/templates/error/' . $contentType . '.php';

            if (file_exists($file)) {
                $this->templateFile = $file;
                $this->themeLocation = $location;
                break;
            }
        }
    }

    /**
     * @var ThemeLocation[]|null
     */
    private ?array $themeLocations = null;

    /**
     * Returns an array of available theme locations, ordered by priority
     * (selected theme first, then default).
     *
     * @return ThemeLocation[]
     */
    public function getThemeLocations(): array
    {
        if (isset($this->themeLocations)) {
            return $this->themeLocations;
        }

        $this->themeLocations = array();

        if (defined('APP_THEME')) {
            $this->registerThemeLocation(
                APP_INSTALL_FOLDER . '/themes/' . APP_THEME,
                APP_URL . '/themes/' . APP_THEME
            );

            $this->registerThemeLocation(
                APP_INSTALL_FOLDER . '/themes/' . APP_THEME,
                APP_INSTALL_URL . '/themes/' . APP_THEME
            );
        }

        $this->registerThemeLocation(
            APP_ROOT . '/themes/default',
            APP_URL . '/themes/default'
        );

        $this->registerThemeLocation(
            APP_INSTALL_FOLDER . '/themes/default',
            APP_INSTALL_URL . '/themes/default'
        );

        return $this->themeLocations;
    }

    private function registerThemeLocation(string $path, string $url): void
    {
        $this->themeLocations[] = new ThemeLocation($path, $url);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAbstract(): string
    {
        return $this->abstract;
    }

    public function getThemePath(): string
    {
        return $this->getThemeLocation()?->getPath() ?? '';
    }

    public function getThemeURL(): string
    {
        return $this->getThemeLocation()?->getURL() ?? '';
    }

    public function getSentContent(): string
    {
        return $this->sentContent;
    }

    /**
     * Attempts to find a file in any of the available theme locations:
     * This searches in the selected theme first -if any- and then in
     * the default theme.
     *
     * @param string $file The relative path to the file, e.g. "img/logo-big.png"
     * @return Application_ErrorDetails_ThemeFile|NULL
     */
    public function findFile(string $file): ?Application_ErrorDetails_ThemeFile
    {
        foreach ($this->getThemeLocations() as $location) {
            $path = $location->getPath() . '/' . $file;
            if (file_exists($path)) {
                return new Application_ErrorDetails_ThemeFile(
                    $path,
                    $location[0] . '/' . $file
                );
            }
        }

        return null;
    }

    public function display(): never
    {
        $templateFile = $this->getTemplateFile();

        if (empty($templateFile)) {
            displayHTML(
                '<h1>' . APP_ERROR_PAGE_TITLE . '</h1>' .
                '<p>The system was unable to load the error page template.</p>' .
                '<p>Raw exception information follows.</p>' .
                '<hr>' .
                $this->renderStack()
            );
        }

        $error = $this;

        require_once $templateFile;

        Application::exit();
    }
}

