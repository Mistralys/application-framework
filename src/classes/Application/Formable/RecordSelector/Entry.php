<?php
/**
 * @package Application
 * @subpackage Formable
 */

declare(strict_types=1);

use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Container for a single entry in the record selector's 
 * options elements list. Used to be able to customize the
 * entries if needed, using the `configureEntry` method.
 *
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see Application_Formable_RecordSelector::configureEntry()
 */
class Application_Formable_RecordSelector_Entry
{
    public const int ERROR_NO_DB_RECORD_SET = 92801;

    private ?DBHelperRecordInterface $record = null;
    private string $label;
    private string $id;
    private string $category = '';

   /**
    * @var array<string,string>
    */
    private array $attributes = array();
    
    public function __construct(string $id, string $label)
    {
        $this->label = $label;
        $this->id = $id;
    }

    public function setRecord(DBHelperRecordInterface $record) : self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * Retrieves the database record tied to this selector entry.
     *
     * NOTE: Available only if a record has been set, which will
     * be the case for the RecordSelector, but not the base selector.
     *
     * @return DBHelperRecordInterface
     * @throws Application_Formable_Exception
     */
    public function getRecord() : DBHelperRecordInterface
    {
        if(isset($this->record))
        {
            return $this->record;
        }

        throw new Application_Formable_Exception(
            'No selector DB record set',
            sprintf(
                'No DB record set for selector entry [%s] with label [%s].',
                $this->getID(),
                $this->getLabel()
            ),
            self::ERROR_NO_DB_RECORD_SET
        );
    }
    
    public function setLabel(string $label) : Application_Formable_RecordSelector_Entry
    {
        $this->label = $label;
        return $this;
    }

    public function setCategory(string $category) : Application_Formable_RecordSelector_Entry
    {
        $this->category = $category;
        return $this;
    }
    
    public function setID(string $id) : Application_Formable_RecordSelector_Entry
    {
        $this->id = $id;
        return $this;
    }
    
    public function setAttribute(string $name, string $value) : Application_Formable_RecordSelector_Entry
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
   /**
    * @return array<string,string>
    */    
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }
}
