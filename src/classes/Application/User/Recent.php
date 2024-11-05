<?php
/**
 * File containing the class {@Application_User_Recent}.
 *
 * @package Application
 * @subpackage User
 * @see Application_User_Recent
 */

declare(strict_types=1);

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Media\Collection\MediaCollection;
use Application\NewsCentral\NewsCollection;
use AppUtils\ConvertHelper;

/**
 * Recent items manager, used to store and access recent items
 * that the user worked on, to display them in the welcome screen.
 *
 * This must be extended by the driver to add the driver-specific
 * setup of categories and items.
 *
 * @package Application
 * @subpackage User
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_User_Recent implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_CATEGORY_ALIAS_NOT_FOUND = 72701;
    public const ERROR_CATEGORY_ALIAS_EXISTS = 72702;
    public const SETTING_PINNED_NOTES = 'notepad-pinned-notes';

    private Application_User $user;

    /**
     * @var Application_User_Recent_Category[]
     */
    private array $categories = array();

    public function __construct(Application_User $user)
    {
        $this->user = $user;

        $this->registerCategories();

        $this->log(sprintf('Registered [%s] categories.', count($this->categories)));
    }

    public function getMaxItemsDefault() : int
    {
        return Application_User_Recent_Category::MAX_ITEMS_DEFAULT;
    }

    abstract protected function registerCategories() : void;

    protected function registerCategory(string $alias, string $label) : Application_User_Recent_Category
    {
        if($this->categoryAliasExists($alias))
        {
            throw new Application_Exception(
                'Recent items c1ategory alias already exists.',
                sprintf(
                    'Cannot add category with alias [%s], a category has already been added with the same alias.',
                    $alias
                ),
                self::ERROR_CATEGORY_ALIAS_EXISTS
            );
        }

        $this->log(sprintf('Register category [%s].', $alias));

        $category = new Application_User_Recent_Category($this, $alias, $label);

        $this->categories[] = $category;

        return $category;
    }

    protected function registerNews() : void
    {
        $this->registerCategory(NewsCollection::RECENT_ITEMS_CATEGORY, t('News articles'));
    }

    protected function registerMedia() : void
    {
        $this->registerCategory(MediaCollection::RECENT_ITEMS_CATEGORY, t('Media documents'));
    }

    public function categoryAliasExists(string $alias) : bool
    {
        $aliases = $this->getCategoryAliases();

        return in_array($alias, $aliases);
    }

    /**
     * @param string $alias
     * @return Application_User_Recent_Category
     * @throws Application_Exception
     *
     * @see Application_User_Recent::ERROR_CATEGORY_ALIAS_NOT_FOUND
     */
    public function getCategoryByAlias(string $alias) : Application_User_Recent_Category
    {
        foreach ($this->categories as $category)
        {
            if($category->getAlias() === $alias)
            {
                return $category;
            }
        }

        throw new Application_Exception(
            'Recent items category not found.',
            sprintf(
                'The category with alias [%s] could not be found. Registered categories are: [%s].',
                $alias,
                implode(', ', $this->getCategoryAliases())
            ),
            self::ERROR_CATEGORY_ALIAS_NOT_FOUND
        );
    }

    /**
     * @return string[]
     */
    public function getCategoryAliases() : array
    {
        $result = array();

        foreach ($this->categories as $category)
        {
            $result[] = $category->getAlias();
        }

        return $result;
    }

    /**
     * @return Application_User
     */
    public function getUser(): Application_User
    {
        return $this->user;
    }

    /**
     * @return Application_User_Recent_Category[]
     */
    public function getCategories(bool $includeHidden=true): array
    {
        if($includeHidden)
        {
            return $this->categories;
        }

        $result = array();
        foreach ($this->categories as $category)
        {
            if($category->getMaxItems() > 0)
            {
                $result[] = $category;
            }
        }

        return $result;
    }

    public function hasEntries() : bool
    {
        foreach ($this->categories as $category)
        {
            if($category->hasEntries())
            {
                return true;
            }
        }

        return false;
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            'User [%s] | Recent items',
            $this->user->getID()
        );
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Welcome::URL_NAME_WELCOME;

        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = Application_Admin_Area_Welcome_Settings::URL_NAME_SETTINGS;

        return $this->getAdminURL($params);
    }

    public function pinNote(Application_User_Notepad_Note $note) : void
    {
        $noteID = $note->getID();
        $ids = $this->getPinnedNoteIDs();

        if(!in_array($noteID, $ids, true))
        {
            $ids[] = $noteID;
        }

        $this->savePinnedNotes($ids);
    }

    public function unpinNote(Application_User_Notepad_Note $note) : void
    {
        $noteID = $note->getID();

        $ids = ConvertHelper::arrayRemoveValues($this->getPinnedNoteIDs(), array($noteID));

        $this->savePinnedNotes($ids);
    }

    private function savePinnedNotes(array $ids) : void
    {
        $this->user->setArraySetting(self::SETTING_PINNED_NOTES, $ids);
        $this->user->saveSettings();
    }

    /**
     * @return int[]
     */
    public function getPinnedNoteIDs() : array
    {
        $ids = $this->user->getArraySetting(self::SETTING_PINNED_NOTES);
        $result = array();
        $notepad = $this->user->getNotepad();

        foreach($ids as $id)
        {
            if($notepad->idExists($id))
            {
                $result[] = $id;
            }
        }

        return $result;
    }

    public function getPinnedNotes() : array
    {
        $result = array();
        $notepad = $this->user->getNotepad();
        $ids = $this->getPinnedNoteIDs();

        foreach($ids as $id)
        {
            $result[] = $notepad->getNoteByID($id);
        }

        return $result;
    }

    public function getCategoriesWithNotes(bool $includeHidden=true) : array
    {
        $categories = $this->getCategories($includeHidden);
        $notes = $this->getPinnedNotes();

        foreach ($notes as $note)
        {
            $categories[] = new Application_User_Recent_NoteCategory($this, $note);
        }

        return $categories;
    }
}
