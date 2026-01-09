<?php
/**
 * @package Application Tests
 * @subpackage Utilities
 */

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Application;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\Interfaces\StringableInterface;

/**
 * Holds information on a test output file that has been
 * written to disk during a test by calling {@see ApplicationTestCase::saveTestFile()}.
 * Has some convenient methods to get information on the file.
 *
 * @package Application Tests
 * @subpackage Utilities
 */
class TestOutputFile implements StringableInterface
{
    private FileInfo $file;
    private string $url;
    private static int $counter = 0;

    public function __construct($content, ?string $extension=null, ?string $name = null)
    {
        self::$counter++;

        $prefix = 'test-file-'.self::$counter;

        if(empty($name)) {
            $name = $prefix;
        } else {
            $name = $prefix.'-'.$name;
        }

        $this->url = Application::getTempFileURL($name, $extension);
        $this->file = FileInfo::factory(Application::getTempFile($name, $extension))
            ->putContents($content);
    }

    public function getFile() : FileInfo
    {
        return $this->file;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function getPath() : string
    {
        return $this->file->getPath();
    }

    public function getRelativePath() : string
    {
        return FileHelper::relativizePath($this->file->getPath(), APP_ROOT);
    }

    /**
     * Gets a string with information on the file, including
     * its URL to open it in a browser for compatible formats.
     *
     * @return string
     */
    public function summarize() : string
    {
        return sprintf(
            'Test file: %s'.PHP_EOL.
            'Size: %s'.PHP_EOL.
            'URL: %s'.PHP_EOL,
            $this->getRelativePath(),
            ConvertHelper::bytes2readable($this->file->getSize()),
            $this->url
        );
    }

    public function __toString()
    {
        return $this->file->getPath();
    }
}
