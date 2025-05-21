<?php
/**
 * @package User Interface
 * @subpackage Data Grids
 */

use AppUtils\ConvertHelper;
use AppUtils\HTMLTag;
use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\ClassableTrait;
use UI\DataGrid\EntryClientCommands;
use UI\DataGrid\GridClientCommands;

/**
 * Container for a single row in a data grid. Offers an API
 * to customize entries, and is used for some advanced features
 * that require setting custom row CSS classes, for example.
 * 
 * @package User Interface
 * @subpackage Data Grids
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
class UI_DataGrid_Entry implements ClassableInterface, ArrayAccess
{
    use ClassableTrait;

    public const ERROR_MISSING_PRIMARY_VALUE = 536001;
    
    protected UI_DataGrid $grid;
    protected string $id;
    private bool $countable = true;

    /**
     * @var array<string, string|int|float|StringableInterface|NULL>
     */
    protected array $data;

    /**
     * @param UI_DataGrid $grid
     * @param array<string, string|int|float|StringableInterface|NULL> $data
     */
    public function __construct(UI_DataGrid $grid, array $data)
    {
        $this->id = nextJSID();
        $this->grid = $grid;
        $this->data = $data;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getCheckboxID() : string
    {
        return $this->id.'_check';
    }

    private ?EntryClientCommands $clientCommands = null;

    /**
     * Gets the helper class used to access client-side commands
     * related to this data grid.
     *
     * @return EntryClientCommands
     */
    public function clientCommands() : EntryClientCommands
    {
        if (!isset($this->clientCommands)) {
            $this->clientCommands = new EntryClientCommands($this);
        }

        return $this->clientCommands;
    }

    public function renderCheckboxLabel(string $label) : string
    {
        return sprintf(
            '<label for="%s" class="grid-check-label">%s</label>',
            $this->getCheckboxID(),
            $label
        );
    }
    
   /**
    * Retrieves the data record for this entry.
    * @return array<int|string,mixed>
    */
    public function getData() : array
    {
        return $this->data;
    }
    
   /**
    * Merges the specified data set with the existing entry data.
    * @param array<int|string,mixed> $data
    * @return $this
    */
    public function setData(array $data) : self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setColumnValue(string $name, $value) : self
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function makeNonCountable() : self
    {
        $this->countable = false;
        return $this;
    }

    /**
     * Whether this entry can be included in the entries' total.
     *
     * @return bool
     * @see UI_DataGrid::countEntries()
     */
    public function isCountable() : bool
    {
        return $this->countable;
    }
    
   /**
    * Styles the row as a warning entry.
    * @return $this
    */
    public function makeWarning() : self
    {
        return $this
            ->addClass('row-type-warning')
            ->addClass('warning');
    }

   /**
    * Styles the row as a success entry.
    * @return $this
    */
    public function makeSuccess() : self
    {
        return $this
            ->addClass('row-type-success')
            ->addClass('success');
    }
    
   /**
    * Avoids this row from being reordered. Note that this is only
    * relevant if the data grid's entries sorting feature has been
    * enabled. Otherwise, it is simply ignored.
    * 
    * Additional note: this only disables the dragging of the row.
    * You have to implement any logic beyond this in your clientside
    * handler class, as it does not prevent the user from moving other
    * rows above or below a non-sortable row, effectively moving it
    * anyway even if indirectly.
    * 
    * @return $this
    */
    public function makeNonSortable() : self
    {
        if($this->grid->isEntriesSortable()) {
            $this->addClass('row-immovable');
        }
        
        return $this;
    }
    
   /**
    * Selects the entry, so it will be pre-selected in the list
    * if the data grid supports multiple selection.
    * 
    * @param bool $select
    * @return $this
    */
    public function select(bool $select=true) : self
    {
        $this->selected = $select;

        if($select) {
            $this->addClass('active');
        } else {
            $this->removeClass('active');
        }

        return $this;
    }

    /**
     * @var bool
     */
    protected bool $selected = false;
    
    public function isSelected() : bool
    {
        return $this->selected;
    }

    /**
     * @return mixed|null
     */
    public function getPrimaryValue()
    {
        return $this->getValue($this->grid->getPrimaryField());
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getValue(string $name)
    {
        if(isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        return null;
    }
    
    public function getValueForColumn(UI_DataGrid_Column $column) : string
    {
        $value = $this->getValue($column->getDataKey());

        if(!is_string($value) && is_callable($value)) {
            $value = $value($this, $column);
        }

        return $this->var2cellText($value);
    }

    public function var2cellText($value) : string
    {
        if($value instanceof DateTime) {
            return ConvertHelper::date2listLabel($value, true, true);
        }

        if(is_string($value)) {
            return $value;
        }

        if(is_bool($value)) {
            return bool2string($value);
        }

        return (string)$value;
    }
    
    public function render() : string
    {
        return (string)HTMLTag::create('tr')
            ->id($this->getID())
            ->addClasses($this->getClasses())
            ->attr('data-refid', $this->getReferenceID())
            ->setContent($this->grid->renderCells($this));
    }

    /**
     * If a primary field is present in the grid, adds the
     * `data-refid` attribute to the row, containing the value
     * of the primary field for the entry. This is used on the
     * client side to access the primary value.
     *
     * @return string
     * @throws UI_DataGrid_Exception
     */
    public function getReferenceID() : string
    {
        $primary = $this->grid->getPrimaryField();

        // if the primary key name is set, we are in a mode where this
        // is required.
        if (empty($primary)) {
            return '';
        }

        if(isset($this->data[$primary])) {
            return(string)$this->data[$primary];
        }

        throw new UI_DataGrid_Exception(
            'Missing primary key value',
            sprintf(
                'Could not find the primary key [%s] in the data record. Only the keys [%s] were present.',
                $primary,
                implode(', ', array_keys($this->data))
            ),
            self::ERROR_MISSING_PRIMARY_VALUE
        );
    }

    // region: Array access interface

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param string $offset
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if(isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        if(array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
        }
    }

    // endregion
}
