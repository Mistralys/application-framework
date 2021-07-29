<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

class Application_LookupItems implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    const ERROR_INVALID_LOOKUP_ITEM_CLASS = 25101;
    const ERROR_ITEM_NOT_FOUND = 25102;

    /**
     * @var string
     */
    protected $driverID;

    /**
     * @var string
     */
    protected $folder;

    /**
     * @var Application_LookupItems_Item[]
     */
    protected $items;

    public function __construct(Application_Driver $driver)
    {
        $this->driverID = $driver->getID();
        
        $this->folder = sprintf(
            '%s/assets/classes/%s/LookupItems',
            APP_ROOT,
            $this->driverID
        );
    }
    
    /**
     * @return Application_LookupItems_Item[]
     * @throws Application_Exception
     * @throws FileHelper_Exception
     */
    public function getItems() : array
    {
        if(isset($this->items)) {
            return $this->items;
        }
        
        $this->items = array();
        
        if(!is_dir($this->folder)) {
            $this->log(sprintf('The driver has no lookup items folder, which is expected at [%s].', $this->folder));
            return $this->items;
        }
        
        $names = FileHelper::createFileFinder($this->folder)
        ->getPHPClassNames();
        
        foreach($names as $name) 
        {
            $this->registerItem($name);
        }
 
        return $this->items;
    }

    /**
     * @param string $name
     * @throws Application_Exception
     */
    private function registerItem(string $name) : void
    {
        $this->log(sprintf('Adding item [%s].', $name));

        $className = sprintf(
            '%s_LookupItems_%s',
            $this->driverID,
            $name
        );

        $item = new $className();
        if($item instanceof Application_LookupItems_Item)
        {
            $this->items[] = $item;
            return;
        }

        throw new Application_Exception(
            'Invalid lookup item',
            sprintf(
                'The lookup item class [%s] does not extend the [%s] base class.',
                $className,
                Application_LookupItems_Item::class
            ),
            self::ERROR_INVALID_LOOKUP_ITEM_CLASS
        );
    }

    /**
     * @param string $id
     * @return Application_LookupItems_Item
     *
     * @throws Application_Exception|FileHelper_Exception
     * @see Application_LookupItems::ERROR_ITEM_NOT_FOUND
     */
    public function getItemByID(string $id) : Application_LookupItems_Item
    {
        $items = $this->getItems();

        foreach ($items as $item)
        {
            if($item->getID() === $id) {
                return $item;
            }
        }

        throw new Application_Exception(
            'Lookup item ID not found',
            sprintf(
                'No lookup item with ID [%s] found.',
                $id
            ),
            self::ERROR_ITEM_NOT_FOUND
        );
    }

    /**
     * @param string $id
     * @return bool
     * @throws Application_Exception
     * @throws FileHelper_Exception
     */
    public function idExists(string $id) : bool
    {
        $items = $this->getItems();

        foreach ($items as $item)
        {
            if($item->getID() === $id)
            {
                return true;
            }
        }

        return false;
    }

    public function getLogIdentifier() : string
    {
        return 'LookupItems';
    }
}
