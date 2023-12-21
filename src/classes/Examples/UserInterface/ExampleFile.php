<?php
/**
 * @package Examples
 * @subpackage User Interface
 */

declare(strict_types=1);

namespace Mistralys\Examples\UserInterface;

use Application\AppFactory;
use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_Appinterface;
use Application_Admin_ScreenInterface;
use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use AppUtils\OutputBuffering;
use TestDriver\ClassFactory;

/**
 * A single UI example file.
 *
 * @package Examples
 * @subpackage User Interface
 */
class ExampleFile implements StringPrimaryRecordInterface
{
    private ExamplesCategory $category;
    private FolderInfo $folder;
    private ArrayDataCollection $info;
    private ?FileInfo $descriptionFile = null;
    private ?FileInfo $codeFile = null;

    public function __construct(ExamplesCategory $category, FolderInfo $folder)
    {
        $this->category = $category;
        $this->folder = $folder;
        $this->info = ArrayDataCollection::create();

        $infoFile = JSONFile::factory($folder->getSubFile('example.json'));
        if($infoFile->exists()) {
            $this->info->setKeys($infoFile->parse());
        }
    }

    public function getCategory(): ExamplesCategory
    {
        return $this->category;
    }

    public function getID(): string
    {
        return $this->folder->getName();
    }

    public function getScreenID() : string
    {
        return $this->category->getID().'.'.$this->getID();
    }

    public function getTitle() : string
    {
        return $this->info->getString('title');
    }

    public function isValid() : bool
    {
        return $this->getTitle() !== '';
    }

    public function getAdminViewURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = Application_Admin_Area_Devel_Appinterface::URL_NAME;
        $params[Application_Admin_Area_Devel_Appinterface::REQUEST_PARAM_EXAMPLE_ID] = $this->getScreenID();

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    public function getDescriptionFile() : FileInfo
    {
        if(is_null($this->descriptionFile)) {
            $this->descriptionFile = FileInfo::factory($this->folder->getSubFile('description.md'));
        }

        return $this->descriptionFile;
    }

    public function getDescription() : string
    {
        $file = $this->getDescriptionFile();
        if($file->exists()) {
            return $file->getContents();
        }

        return '';
    }

    public function getCodeFile() : FileInfo
    {
        if(!isset($this->codeFile)) {
            $this->codeFile = FileInfo::factory($this->folder->getSubFile('code.php'));
        }

        return $this->codeFile;
    }

    public function hasOutput() : bool
    {
        return $this->info->getBool('disableOutput') !== true;
    }

    public function renderOutput() : string
    {
        $activeExampleID = $this->getScreenID();
        $activeURL = $this->getAdminViewURL();

        OutputBuffering::start();
        include $this->getCodeFile()->getPath();
        return OutputBuffering::get();
    }

    public function getSourceCode() : string
    {
        return $this->getCodeFile()->getContents();
    }
}
