<?php
/**
 * File containing the {@link DBHelper_BaseCollection_OperationContext} class.
 * @package Application
 * @subpackage DBHelper
 * @see DBHelper_BaseCollection_OperationContext
 */

declare(strict_types=1);

use AppUtils\Interface_Optionable;
use AppUtils\Traits_Optionable;

/**
 * Abstract base class for contexts used when a
 * collection record is created or deleted, to
 * ensure that the operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseCollection_OperationContext implements Interface_Optionable
{
    use Traits_Optionable;

    /**
     * @var string
     */
    protected $contextID;

    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;

    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;

    /**
     * @var bool
     */
    protected $silent = false;

    /**
     * @var int 
     */
    protected static $contextIDCounter = 0;

    public function __construct(DBHelper_BaseRecord $record)
    {
        self::$contextIDCounter++;

        $this->contextID = 'context'.self::$contextIDCounter;
        $this->collection = $record->getCollection();
        $this->record = $record;
    }

    public function getDefaultOptions(): array
    {
        return array();
    }

    public function getID() : string
    {
        return $this->contextID;
    }

    public function makeSilent() : void
    {
        $this->silent = true;
    }

    public function isSilent() : bool
    {
        return $this->silent;
    }

    /**
     * @return DBHelper_BaseRecord
     */
    public function getRecord(): DBHelper_BaseRecord
    {
        return $this->record;
    }

    public function getCollection() : DBHelper_BaseCollection
    {
        return $this->collection;
    }
}
