<?php
/**
 * File containing the {@link UI_DataGrid_Entry} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_DataGrid_Entry
 */

use AppUtils\ConvertHelper;
use AppUtils\Traits_Classable;
use AppUtils\Interface_Classable;

/**
 * Container for a single row in a data grid. Offers an API
 * to customize entries, and is used for some advanced features
 * that require setting custom row classes for example.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
class UI_DataGrid_Entry implements Interface_Classable, ArrayAccess
{
    use Traits_Classable;

    public const ERROR_MISSING_PRIMARY_VALUE = 536001;
    
   /**
    * @var UI_DataGrid
    */
    protected $grid;
    
   /**
    * @var array<string,mixed>
    */
    protected $data;
    
    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    private $countable = true;

    public function __construct(UI_DataGrid $grid, $data)
    {
        $this->id = nextJSID();
        $this->grid = $grid;
        $this->data = $data;
    }
    
    public function getID()
    {
        return $this->id;
    }
    
    public function getCheckboxID()
    {
        return $this->id.'_check';
    }

    public function renderCheckboxLabel(string $label) : string
    {
        return sprintf(
            '<label for="%s">%s</label>',
            $this->getCheckboxID(),
            $label
        );
    }
    
   /**
    * Retrieves the data record for this entry.
    * @return array
    */
    public function getData()
    {
        return $this->data;
    }
    
   /**
    * Merges the specified data set with the existing entry data.
    * @param array<string,mixed> $data
    * @return UI_DataGrid_Entry
    */
    public function setData(array $data) : UI_DataGrid_Entry
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function setColumnValue($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @return $this
     */
    public function makeNonCountable()
    {
        $this->countable = false;
        return $this;
    }

    /**
     * Whether this entry can be included in the entries total.
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
    * @return UI_DataGrid_Entry
    */
    public function makeWarning()
    {
        return $this->addClass('row-type-warning')->addClass('warning');
    }

   /**
    * Styles the row as a success entry.
    * @return UI_DataGrid_Entry
    */
    public function makeSuccess()
    {
        return $this->addClass('row-type-success')->addClass('success');
    }
    
   /**
    * Avoids this row from being reordered. Note that this is only
    * relevant if the data grid's entries sorting feature has been
    * enabled. Otherwise it is simply ignored. 
    * 
    * Additional note: this only disables the dragging of the row.
    * You have to implement any logic beyond this in your clientside
    * handler class, as it does not prevent the user from moving other
    * rows above or below an unsortable row, effectively moving it 
    * anyway even if indirectly.
    * 
    * @return $this
    */
    public function makeNonSortable()
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
    public function select(bool $select=true)
    {
        $this->selected = $select;
        return $this;
    }

    /**
     * @var bool
     */
    protected $selected = false;
    
    public function isSelected() : bool
    {
        return $this->selected;
    }
    
    public function getPrimaryValue()
    {
        return $this->getValue($this->grid->getPrimaryField());
    }
    
    public function getValue($name)
    {
        if(isset($this->data[$name])) {
            return $this->data[$name];
        }
        
        return null;
    }
    
    public function getValueForColumn(UI_DataGrid_Column $column) : string
    {
        $value = $this->getValue($column->getDataKey());
        
        if($value instanceof DateTime)
        {
            return ConvertHelper::date2listLabel($value, true, true);
        }

        return (string)$value;
    }
    
    public function render()
    {
        $primary = $this->grid->getPrimaryField();

        $attribs = array(
            'class' => $this->classesToString(),
        );
        
        // if the primary key name is set, we are in a mode where this
        // is required.
        if (!empty($primary)) {
            if(!isset($this->data[$primary])) {
                throw new Application_Exception(
                    'Missing primary key value',
                    sprintf(
                        'Could not find the primary key [%s] in the data record. Only the keys [%s] were present.',
                        $primary,
                        implode(', ', array_keys($this->data))
                    ),
                    self::ERROR_MISSING_PRIMARY_VALUE
                );
            }
            
            $attribs['data-refid'] = $this->data[$primary];
        }
        
        $html =
        '<tr '.compileAttributes($attribs).'>' .
            $this->grid->renderCells($this) .
        '</tr>';
        
        return $html;
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