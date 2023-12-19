<?php
/**
 * @package Examples
 * @subpackage User Interface
 */

declare(strict_types=1);

namespace Mistralys\Examples\UserInterface;

use AppUtils\ArrayDataCollection;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Category of UI examples.
 *
 * @package Examples
 * @subpackage User Interface
 *
 * @method ExampleFile[] getAll()
 * @method ExampleFile getByID(string $id)
 * @method ExampleFile getDefault()
 */
class ExamplesCategory extends BaseStringPrimaryCollection implements StringPrimaryRecordInterface
{
    private FolderInfo $folder;
    private ArrayDataCollection $info;

    public function __construct(FolderInfo $folder)
    {
        $this->folder = $folder;
        $this->info = new ArrayDataCollection();

        $this->initItems();
    }

    public function getID(): string
    {
        return $this->folder->getName();
    }

    public function getDefaultID(): string
    {
        $ids = $this->getIDs();
        if(!empty($ids)) {
            return array_shift($ids);
        }

        return '';
    }

    public function getFolder() : FolderInfo
    {
        return $this->folder;
    }

    public function getTitle() : string
    {
        return $this->info->getString('title');
    }

    public function getSummary() : string
    {
        return $this->info->getString('summary');
    }

    public function isValid() : bool
    {
        return $this->getTitle() !== '' && $this->countRecords() > 0;
    }

    protected function registerItems(): void
    {
        $infoFile = $this->folder->getSubFile('category.json');
        if(!$infoFile->exists()) {
            return;
        }

        $this->info->setKeys(JSONFile::factory($infoFile)->parse());

        $folders = $this->folder->getSubFolders();

        foreach($folders as $folder)
        {
            $example = new ExampleFile($this, $folder);
            if($example->isValid()) {
                $this->registerItem($example);
            }
        }
    }
}
