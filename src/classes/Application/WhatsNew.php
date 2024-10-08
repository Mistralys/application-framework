<?php
/**
 * File containing the class {@see \Application\WhatsNew}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew
 */

declare(strict_types=1);

namespace Application;

use Application\Driver\DriverException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\WhatsNew\WhatsNewException;
use Application\WhatsNew\WhatsNewImage;
use Application\WhatsNew\XMLFileWriter;
use Application\WhatsNew\PlainTextRenderer;
use Application\WhatsNew\XMLRenderer;
use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_WhatsNewEditor;
use Application_Admin_Area_Devel_WhatsNewEditor_Create;
use Application_Admin_Area_Devel_WhatsNewEditor_List;
use Application_Driver;
use Application_Exception;
use Application\WhatsNew\AppVersion;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Handles reading the application's `WHATSNEW.xml` file, as
 * well as offering methods to modify it and convert the contents
 * to different output formats.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WhatsNew
{
    public const ERROR_WHATS_NEW_FILE_NOT_FOUND = 30001;
    public const ERROR_COULD_NOT_PARSE_XML = 30002;
    public const ERROR_VERSION_NUMBER_NOT_FOUND = 30003;

    protected string $file;
    private MarkdownRenderer $markdownRenderer;

    /**
     * @var AppVersion[]
     */
    protected array $versions = array();

    public function __construct(string $sourceFile)
    {
        $this->file = $sourceFile;
        $this->markdownRenderer = MarkdownRenderer::create();

        if (!file_exists($this->file))
        {
            throw new Application_Exception(
                sprintf('Could not find file [%s].', basename($this->file)),
                '',
                self::ERROR_WHATS_NEW_FILE_NOT_FOUND
            );
        }

        $this->parse();
    }

    public static function getDeveloperLangID() : string
    {
        return getClassTypeName(AppVersion\VersionLanguage\DEV::class);
    }

    public function getPath() : string
    {
        return $this->file;
    }

    /**
     * @return MarkdownRenderer
     */
    public function getMarkdownRenderer() : MarkdownRenderer
    {
        return $this->markdownRenderer;
    }

    protected function parse() : void
    {
        $xml = simplexml_load_string(FileHelper::readContents($this->file));

        if ($xml === false)
        {
            throw new Application_Exception(
                sprintf('Could not read file [%s].', basename($this->file)),
                'Trying to parse the XML failed. Syntax error?',
                self::ERROR_COULD_NOT_PARSE_XML
            );
        }

        foreach ($xml->version as $versionNode)
        {
            $this->versions[] = new AppVersion($this, $versionNode);
        }
    }

    /**
     * Retrieves the current (highest) version.
     *
     * @return AppVersion|NULL
     */
    public function getCurrentVersion() : ?AppVersion
    {
        if (!empty($this->versions))
        {
            return $this->versions[0];
        }

        return null;
    }

    /**
     * @return AppVersion[]
     */
    public function getVersions() : array
    {
        return $this->versions;
    }

    /**
     * @param string $langID
     * @return AppVersion[]
     */
    public function getVersionsByLanguage(string $langID) : array
    {
        $result = array();
        foreach ($this->versions as $version)
        {
            if ($version->hasLanguage($langID))
            {
                $result[] = $version;
            }
        }

        return $result;
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminCreateURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = Application_Admin_Area_Devel_WhatsNewEditor_Create::URL_NAME;

        return $this->getAdminURL($params);
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = Application_Admin_Area_Devel_WhatsNewEditor::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminListURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = Application_Admin_Area_Devel_WhatsNewEditor_List::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function addVersion(string $version) : AppVersion
    {
        $xml = sprintf('<version id="%s"></version>', $version);

        $node = simplexml_load_string($xml);
        $instance = new AppVersion($this, $node);

        array_unshift($this->versions, $instance);

        return $instance;
    }

    public function writeToDisk() : void
    {
        (new XMLFileWriter($this))->write($this->getPath());
    }

    public function toPlainText(string $langID) : string
    {
        return (new PlainTextRenderer($this))->render($langID);
    }

    public function toXML() : string
    {
        return (new XMLRenderer($this))->render();
    }

    public function getByRequest() : ?AppVersion
    {
        $number = (string)Application_Driver::getInstance()
            ->getRequest()
            ->registerParam(AppVersion::REQUEST_PARAM_NUMBER)
            ->get();

        if($this->numberExists($number))
        {
            return $this->getByNumber($number);
        }

        return null;
    }

    public function getByNumber(string $number) : AppVersion
    {
        $versions = $this->getVersions();

        foreach($versions as $version)
        {
            if($version->getNumber() === $number)
            {
                return $version;
            }
        }

        throw new WhatsNewException(
            'Application version not found by number.',
            sprintf(
                'Tried getting version number [%s].',
                $number
            ),
            self::ERROR_VERSION_NUMBER_NOT_FOUND
        );
    }

    public function numberExists(string $number) : bool
    {
        $versions = $this->getVersions();

        foreach($versions as $version)
        {
            if($version->getNumber() === $number)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return WhatsNewImage[]
     * @throws DriverException
     * @throws FileHelper_Exception
     */
    public function getAvailableImages() : array
    {
        $path = Application_Driver::getInstance()->getTheme()->getDriverImagesPath().'/whatsnew';

        if(!is_dir($path)) {
            return array();
        }

        $files = FileHelper::createFileFinder($path)
            ->includeExtensions(array('png', 'jpg'))
            ->setPathmodeAbsolute()
            ->getAll();

        usort($files, static function(string $a, string $b) : int {
            return filemtime($a) - filemtime($b);
        });

        $result = array();
        foreach($files as $file) {
            $result[] = new WhatsNewImage($file);
        }

        return $result;
    }
}
