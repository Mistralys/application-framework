<?php
/**
 * @package Examples
 * @subpackage User Interface
 * @see \Mistralys\Examples\InterfaceExamples
 */

declare(strict_types=1);

namespace Mistralys\Examples;

use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper\FolderInfo;
use Mistralys\Examples\UserInterface\ExamplesCategory;
use UI;

/**
 * Helper class used to navigate the application's UI examples.
 *
 * Fetches information on the available examples from the theme
 * folder, and provides methods to access them.
 *
 * @package Examples
 * @subpackage User Interface
 *
 * @method ExamplesCategory[] getAll()
 * @method ExamplesCategory getByID(string $id)
 * @method ExamplesCategory getDefault()
 */
class InterfaceExamples extends BaseStringPrimaryCollection
{
    private FolderInfo $folder;

    public function __construct()
    {
        $this->folder = FolderInfo::factory(UI::getInstance()->getTheme()->getDefaultTemplatesPath().'/appinterface');

        $this->initItems();
    }

    public function getDefaultID(): string
    {
        $ids = $this->getIDs();
        if(!empty($ids)) {
            return array_shift($ids);
        }

        return '';
    }

    protected function registerItems(): void
    {
        if(!$this->folder->exists()) {
            return;
        }

        $folders = $this->folder->getSubFolders();

        foreach ($folders as $folder) {
            $category = new ExamplesCategory($folder);

            if($category->isValid()) {
                $this->registerItem($category);
            }
        }
    }
}
