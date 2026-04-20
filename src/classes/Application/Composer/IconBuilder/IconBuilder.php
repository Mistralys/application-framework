<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

use AppUtils\OperationResult;

/**
 * Orchestrator that reads icon definitions from a JSON source file, renders
 * PHP and JS method blocks, and replaces the content between the
 * {@see self::MARKER_START} and {@see self::MARKER_END} marker comments in
 * the respective target files.
 *
 * Designed for build-time use and has no dependency on the full application
 * bootstrap, the `LocalFrameworkClone`, or the `t()` translation function.
 *
 * @package Application
 * @subpackage Composer
 * @see IconsReader
 * @see PHPRenderer
 * @see JSRenderer
 */
class IconBuilder
{
    public const int ERROR_PHP_ICON_FILE_NOT_FOUND = 82301;
    public const int ERROR_JS_ICON_FILE_NOT_FOUND = 82302;
    public const int ERROR_START_MARKER_NOT_FOUND = 82303;
    public const int ERROR_WRITE_FAILED = 82304;
    public const int ERROR_END_MARKER_NOT_FOUND = 82305;
    public const int ERROR_READ_FAILED = 82306;

    private const string MARKER_START = '/* START METHODS */';
    private const string MARKER_END = '/* END METHODS */';

    private string $iconsJsonPath;
    private string $phpFilePath;
    private string $jsFilePath;
    private ?IconsReader $iconsReader = null;

    /**
     * @param string $iconsJsonPath Path to the icons.json source file.
     * @param string $phpFilePath   Path to the PHP target file.
     * @param string $jsFilePath    Path to the JS target file.
     */
    public function __construct(
        string $iconsJsonPath,
        string $phpFilePath,
        string $jsFilePath
    )
    {
        $this->iconsJsonPath = $iconsJsonPath;
        $this->phpFilePath = $phpFilePath;
        $this->jsFilePath = $jsFilePath;
    }

    /**
     * Returns the icons reader, creating it on first access.
     *
     * @return IconsReader
     */
    public function getIcons() : IconsReader
    {
        if($this->iconsReader === null)
        {
            $this->iconsReader = new IconsReader($this->iconsJsonPath);
        }

        return $this->iconsReader;
    }

    /**
     * Rebuilds the icon methods in both target PHP and JS files.
     *
     * Reads icon definitions from the JSON source, renders method code for
     * each language, and replaces the content between the
     * `START METHODS` / `END METHODS` marker comments in each target file.
     *
     * Returns a failed {@see OperationResult} when any of the following occur:
     * - The PHP target file does not exist ({@see self::ERROR_PHP_ICON_FILE_NOT_FOUND}).
     * - The JS target file does not exist ({@see self::ERROR_JS_ICON_FILE_NOT_FOUND}).
     * - A start marker is absent in either target file ({@see self::ERROR_START_MARKER_NOT_FOUND}).
     * - An end marker is absent in either target file ({@see self::ERROR_END_MARKER_NOT_FOUND}).
     * - Reading a target file fails (e.g. permission denied) ({@see self::ERROR_READ_FAILED}).
     * - Writing a target file fails (e.g. read-only file, full disk) ({@see self::ERROR_WRITE_FAILED}).
     *
     * @return OperationResult
     */
    public function build() : OperationResult
    {
        $result = new OperationResult($this);

        if(!file_exists($this->phpFilePath))
        {
            return $result->makeError(
                sprintf('PHP icon file not found: [%s].', $this->phpFilePath),
                self::ERROR_PHP_ICON_FILE_NOT_FOUND
            );
        }

        if(!file_exists($this->jsFilePath))
        {
            return $result->makeError(
                sprintf('JS icon file not found: [%s].', $this->jsFilePath),
                self::ERROR_JS_ICON_FILE_NOT_FOUND
            );
        }

        $icons = $this->getIcons();

        $phpResult = $this->insertIconCode(
            $this->phpFilePath,
            (new PHPRenderer($icons))->render(),
            $result
        );

        if(!$phpResult->isValid())
        {
            return $phpResult;
        }

        $jsResult = $this->insertIconCode(
            $this->jsFilePath,
            (new JSRenderer($icons))->render(),
            $result
        );

        if(!$jsResult->isValid())
        {
            return $jsResult;
        }

        return $result->makeSuccess(sprintf(
            'Successfully rebuilt icon methods from [%s].',
            basename($this->iconsJsonPath)
        ));
    }

    /**
     * Reads the target file, replaces the content between
     * {@see self::MARKER_START} and {@see self::MARKER_END} with the
     * rendered code, and writes the result back to the file.
     *
     * Sets the supplied result to an error state and returns it when:
     * - The file cannot be read ({@see self::ERROR_READ_FAILED}).
     * - Either marker is absent from the file.
     *
     * @param string $filePath  Absolute path of the target file.
     * @param string $rendered  Rendered method code to insert between the markers.
     * @param OperationResult $result Shared result instance for error reporting.
     * @return OperationResult The same result instance (possibly set to error).
     */
    private function insertIconCode(string $filePath, string $rendered, OperationResult $result) : OperationResult
    {
        $content = @file_get_contents($filePath);

        if($content === false)
        {
            return $result->makeError(
                sprintf('Failed to read file [%s].', $filePath),
                self::ERROR_READ_FAILED
            );
        }

        $startPos = strpos($content, self::MARKER_START);

        if($startPos === false)
        {
            return $result->makeError(
                sprintf(
                    'Start marker [%s] not found in file [%s].',
                    self::MARKER_START,
                    $filePath
                ),
                self::ERROR_START_MARKER_NOT_FOUND
            );
        }

        $endPos = strpos($content, self::MARKER_END);

        if($endPos === false)
        {
            return $result->makeError(
                sprintf(
                    'End marker [%s] not found in file [%s].',
                    self::MARKER_END,
                    $filePath
                ),
                self::ERROR_END_MARKER_NOT_FOUND
            );
        }

        $afterStart = $startPos + strlen(self::MARKER_START);

        $newContent =
            substr($content, 0, $afterStart) .
            $rendered .
            substr($content, $endPos);

        // @ suppresses the native PHP warning; the return value is the authoritative failure signal.
        $written = @file_put_contents($filePath, $newContent);

        if($written === false)
        {
            return $result->makeError(
                sprintf('Failed to write to file [%s].', $filePath),
                self::ERROR_WRITE_FAILED
            );
        }

        return $result;
    }
}
