<?php

declare(strict_types=1);

use Application\AppFactory;
use Application\LookupItems\BaseLookupItem;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;
use UI\AdminURLs\AdminURLInterface;

class Application_User_Recent_Category implements OptionableInterface, Application_Interfaces_Loggable, Application_Interfaces_Iconizable
{
    use OptionableTrait;
    use Application_Traits_Loggable;
    use Application_Traits_Iconizable;

    public const int ERROR_RECENT_ENTRY_NOT_FOUND = 72801;

    public const string OPTION_MAX_ITEMS = 'max-items';
    public const int MAX_ITEMS_DEFAULT = 10;

    /**
     * Maximum number of entries to keep in storage
     */
    public const int STORAGE_MAX_ITEMS = 60;

    public const string REQUEST_PARAM_CLEAR_CATEGORY = 'clear-category';

    private string $label;
    private string $alias;
    protected Application_User_Recent $recent;
    private string $settingName;
    private Application_User $user;
    private ?BaseLookupItem $lookupItem = null;

    /**
     * @var Application_User_Recent_Entry[]|NULL
     */
    private ?array $entries = null;

    public function __construct(Application_User_Recent $recent, string $alias, string $label)
    {
        $this->recent = $recent;
        $this->alias = $alias;
        $this->label = $label;
        $this->user = $this->recent->getUser();
        $this->settingName = 'recent_entries_'.$this->alias;
    }

    public function getDefaultOptions(): array
    {
        return array_merge(
            array(
                self::OPTION_MAX_ITEMS => self::MAX_ITEMS_DEFAULT
            ),
            $this->user->getArraySetting($this->settingName.'-options')
        );
    }

    public function setMaxItems(int $max) : Application_User_Recent_Category
    {
        $this->setOption(self::OPTION_MAX_ITEMS, $max);
        $this->saveOptions();

        return $this;
    }

    public function getMaxItems() : int
    {
        return $this->getIntOption(self::OPTION_MAX_ITEMS);
    }

    /**
     * Adds a new recent item entry to the category.
     *
     * NOTE: If an item with the same ID already exists,
     * the existing item is overwritten with a new instance,
     * which means that the timestamp is updated automatically.
     *
     * @param string $id The unique ID of the item, which can be used to retrieve it again later.
     * @param string $label The item label to show in the UI.
     * @param string|AdminURLInterface $url A URL to open to view the item.
     * @param DateTime|null $date A specific date and time, or null to use the current time.
     * @return Application_User_Recent_Entry
     * @throws Application_Exception
     */
    public function addEntry(string $id, string $label, string|AdminURLInterface $url, ?DateTime $date=null) : Application_User_Recent_Entry
    {
        $this->log('Entry [%s] | Adding as new entry.', $id, $label);

        if(!$date)
        {
            $date = new DateTime();
        }

        if($this->entryIDExists($id))
        {
            $this->log('Entry [%s] | Already exists.', $id);
            $this->unregisterEntry($this->getEntryByID($id));
        }

        $entry = $this->registerEntry($id,$label, (string)$url, $date);

        $this->save();

        return $entry;
    }

    private function registerEntry(string $id, string $label, string $url, DateTime $date) : Application_User_Recent_Entry
    {
        $this->log(sprintf('Entry [%s] | Registering the entry @%s.', $id, $date->format('Y-m-d H:i:s')));

        $entry = new Application_User_Recent_Entry($this, $id, $label, $url, $date);

        $entries = $this->getEntries();
        $entries[] = $entry;

        usort($entries, static function (Application_User_Recent_Entry $a, Application_User_Recent_Entry $b) {
            return $b->getDate() <=> $a->getDate();
        });

        $total = count($entries);

        if($total > self::STORAGE_MAX_ITEMS)
        {
            $entries = array_slice($entries, 0, self::STORAGE_MAX_ITEMS);
        }

        $this->entries = $entries;

        return $entry;
    }

    /**
     * Removes the specified entry from the category.
     *
     * @return $this
     */
    public function removeEntry(Application_User_Recent_Entry $entry) : Application_User_Recent_Category
    {
        $this->unregisterEntry($entry);

        $this->save();

        return $this;
    }

    /**
     * Removes an entry from the category, without saving.
     */
    private function unregisterEntry(Application_User_Recent_Entry $entry) : void
    {
        $this->log('Entry [%s] | Unregistering the entry.', $entry->getID());

        $keep = array();
        $removeID = $entry->getID();

        foreach ($this->getEntries() as $existing)
        {
            if($existing->getID() !== $removeID)
            {
                $keep[] = $existing;
            }
        }

        $this->entries = $keep;
    }

    /**
     * @return Application_User_Recent_Entry[]
     */
    public function getEntries() : array
    {
        $this->loadEntries();

        return $this->entries;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string[]
     */
    public function getEntryIDs() : array
    {
        $result = array();

        foreach($this->getEntries() as $entry)
        {
            $result[] = $entry->getID();
        }

        return $result;
    }

    public function entryIDExists(string $id) : bool
    {
        return in_array($id, $this->getEntryIDs());
    }

    /**
     * @param string $id
     * @return Application_User_Recent_Entry
     * @throws Application_Exception
     * @see Application_User_Recent_Category::ERROR_RECENT_ENTRY_NOT_FOUND
     */
    public function getEntryByID(string $id) : Application_User_Recent_Entry
    {
        foreach($this->getEntries() as $entry)
        {
            if($entry->getID() === $id)
            {
                return $entry;
            }
        }

        throw new Application_Exception(
            'Recent entry not found.',
            sprintf(
                'No entry found with the ID [%s].',
                $id
            ),
            self::ERROR_RECENT_ENTRY_NOT_FOUND
        );
    }

    private function loadEntries() : void
    {
        if(isset($this->entries)) {
            return;
        }

        $this->log('Loading entries.');

        $this->entries = array();

        $entries = $this->recent->getUser()->getArraySetting($this->settingName);

        foreach($entries as $def)
        {
            $this->registerEntry($def['id'], $def['label'], $def['url'], new DateTime($def['date']));
        }

        usort($this->entries, static function (Application_User_Recent_Entry $a, Application_User_Recent_Entry $b) {
            return $b->getDate() <=> $a->getDate();
        });
    }

    private function saveOptions() : void
    {
        $this->user->setArraySetting($this->settingName.'-options', $this->options);
        $this->user->saveSettings();
    }

    private function save() : void
    {
        if(!isset($this->entries)) {
            $this->log('Save | SKIP | No entries to save.');
            return;
        }

        $entries = $this->getEntries();

        $this->log(sprintf('Save | Found [%s] entries to save.', count($entries)));

        $data = array();

        foreach ($entries as $entry)
        {
            $data[] = $entry->toArray();
        }

        $this->user->setArraySetting($this->settingName, $data);
        $this->user->saveSettings();
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            '%s | Recent items | Category [%s]',
            $this->recent->getUser()->getLogIdentifier(),
            $this->getAlias()
        );
    }

    public function hasEntries() : bool
    {
        return !empty($this->getEntries());
    }

    public function getAdminURLClear(array $params=array()) : string
    {
        $params[self::REQUEST_PARAM_CLEAR_CATEGORY] = $this->getAlias();

        return $this->recent->getAdminURL($params);
    }

    public function clearEntries() : Application_User_Recent_Category
    {
        $this->entries = array();

        $this->save();

        return $this;
    }

    /**
     * @param string $id
     * @return $this
     * @throws Application_Exception
     * @throws FileHelper_Exception
     */
    public function setLookupItemID(string $id) : Application_User_Recent_Category
    {
        return $this->setLookupItem(AppFactory::createLookupItems()->getItemByID($id));
    }

    /**
     * @param BaseLookupItem $item
     * @return $this
     */
    public function setLookupItem(BaseLookupItem $item) : Application_User_Recent_Category
    {
        $this->lookupItem = $item;
        return $this;
    }

    public function getLookupItem() : ?BaseLookupItem
    {
        return $this->lookupItem;
    }
}
