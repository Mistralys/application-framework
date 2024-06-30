<?php
/**
 * @package Application
 * @subpackage Lookup Items
 */

declare(strict_types=1);

use Application\LookupItems\BaseDBCollectionLookupItem;
use Application\LookupItems\BaseLookupItem;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Manager class for the item lookup dialog (aka quick search).
 *
 * Every data type that can be searched for in the dialog must
 * have its own class in the application's <code>assets/LookupItems</code>
 * folder. The class must extend {@see BaseLookupItem}, or one
 * of its specialized flavors, like {@see BaseDBCollectionLookupItem}.
 *
 * The rest is handled automatically.
 *
 * @package Application
 * @subpackage Lookup Items
 */
class Application_LookupItems implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_INVALID_LOOKUP_ITEM_CLASS = 25101;
    public const ERROR_ITEM_NOT_FOUND = 25102;

    protected string $driverID;
    protected string $folder;

    /**
     * @var BaseLookupItem[]|NULL
     */
    protected ?array $items = null;

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
     * @return BaseLookupItem[]
     * @throws BaseClassHelperException
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
     * @throws BaseClassHelperException
     */
    private function registerItem(string $name) : void
    {
        $this->log(sprintf('Adding item [%s].', $name));

        $className = ClassHelper::requireResolvedClass(sprintf(
            '%s_LookupItems_%s',
            $this->driverID,
            $name
        ));

        $this->items[] = ClassHelper::requireObjectInstanceOf(
            BaseLookupItem::class,
            new $className(),
            self::ERROR_INVALID_LOOKUP_ITEM_CLASS
        );
    }

    /**
     * @param string $id
     * @return BaseLookupItem
     *
     * @throws Application_Exception|FileHelper_Exception
     * @see Application_LookupItems::ERROR_ITEM_NOT_FOUND
     */
    public function getItemByID(string $id) : BaseLookupItem
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
