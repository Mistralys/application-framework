<?php
/**
 * File containing the {@link DBHelper_BaseCollection_OperationContext} class.
 * @package Application
 * @subpackage DBHelper
 * @see DBHelper_BaseCollection_OperationContext
 */

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

/**
 * Abstract base class for contexts used when a
 * collection record is created or deleted, to
 * ensure that the operation is authentic.
 *
 * @package Application
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class DBHelper_BaseCollection_OperationContext implements OptionableInterface
{
    use OptionableTrait;

    protected string $contextID;
    protected DBHelper_BaseCollection $collection;
    protected DBHelper_BaseRecord $record;
    protected bool $silent = false;
    protected static int $contextIDCounter = 0;

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
