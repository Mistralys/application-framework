<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

class Application_User_Recent_Category implements OptionableInterface, Application_Interfaces_Loggable, Application_Interfaces_Iconizable
{
    use OptionableTrait;
    use Application_Traits_Loggable;
    use Application_Traits_Iconizable;

    public const ERROR_RECENT_ENTRY_NOT_FOUND = 72801;

    const OPTION_MAX_ITEMS = 'max-items';
    const MAX_ITEMS_DEFAULT = 10;

    /**
     * Maximum amount of entries to keep in storage
     */
    const STORAGE_MAX_ITEMS = 60;

    const REQUEST_PARAM_CLEAR_CATEGORY = 'clear-category';

    /**
     * @var string
     */
    private $label;

    /**
     * @var Application_User_Recent_Entry[]
     */
    private $entries = array();

    /**
     * @var string
     */
    private $alias;

    /**
     * @var Application_User_Recent
     */
    protected $recent;

    /**
     * @var string
     */
    private $settingName;

    /**
     * @var Application_User
     */
    private $user;

    /**
     * @var Application_LookupItems_Item|NULL
     */
    private $lookupItem = null;

    public function __construct(Application_User_Recent $recent, string $alias, string $label)
    {
        $this->recent = $recent;
        $this->alias = $alias;
        $this->label = $label;
        $this->user = $this->recent->getUser();
        $this->settingName = 'recent_entries_'.$this->alias;

        $this->log(sprintf('Setting name [%s].', $this->settingName));

        $this->loadEntries();
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
     * @param string $url An URL to open to view the item.
     * @param DateTime|null $date A specific date and time, or null to use the current time.
     * @return Application_User_Recent_Entry
     * @throws Application_Exception
     */
    public function addEntry(string $id, string $label, string $url, ?DateTime $date=null) : Application_User_Recent_Entry
    {
        if(!$date)
        {
            $date = new DateTime();
        }

        if($this->entryIDExists($id))
        {
            $this->unregisterEntry($this->getEntryByID($id));
        }

        $entry = $this->registerEntry($id,$label, $url, $date);

        $this->save();

        return $entry;
    }

    private function registerEntry(string $id, string $label, string $url, DateTime $date) : Application_User_Recent_Entry
    {
        $this->log(sprintf('Registering new entry [%s %s].', $id, $date->format('Y-m-d H:i:s')));

        $entry = new Application_User_Recent_Entry($this, $id, $label, $url, $date);

        $this->entries[] = $entry;

        usort($this->entries, function (Application_User_Recent_Entry $a, Application_User_Recent_Entry $b) {
            return $b->getDate() <=> $a->getDate();
        });

        $total = count($this->entries);

        if($total > self::STORAGE_MAX_ITEMS)
        {
            $this->entries = array_slice($this->entries, 0, self::STORAGE_MAX_ITEMS);
        }

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
        $keep = array();
        $removeID = $entry->getID();

        foreach ($this->entries as $existing)
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

        foreach($this->entries as $entry)
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
        foreach($this->entries as $entry)
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
        $this->log('Loading entries.');

        $entries = $this->recent->getUser()->getArraySetting($this->settingName);

        foreach($entries as $def)
        {
            $this->registerEntry($def['id'], $def['label'], $def['url'], new DateTime($def['date']));
        }

        usort($this->entries, function (Application_User_Recent_Entry $a, Application_User_Recent_Entry $b) {
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
        $this->log(sprintf('Saving with [%s] entries.', count($this->entries)));

        $data = array();

        foreach ($this->entries as $entry)
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
        return !empty($this->entries);
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
     * @param Application_LookupItems_Item $item
     * @return $this
     */
    public function setLookupItem(Application_LookupItems_Item $item) : Application_User_Recent_Category
    {
        $this->lookupItem = $item;
        return $this;
    }

    public function getLookupItem() : ?Application_LookupItems_Item
    {
        return $this->lookupItem;
    }
}
