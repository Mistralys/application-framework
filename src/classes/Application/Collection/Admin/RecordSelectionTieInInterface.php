<?php
/**
 * @package Application
 * @subpackage Collections
 */

declare(strict_types=1);

namespace Application\Collection\Admin;

use Application\Collection\CollectionException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_CollectionItemInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * @package Application
 * @subpackage Collections
 */
interface RecordSelectionTieInInterface
{
    public const COMPACT_LIST_THRESHOLD = 10;

    public function getScreen() : AdminScreenInterface;

    /**
     * Gets the name of the primary request variable that
     * is used to select the record.
     *
     * @return string
     */
    public function getRequestPrimaryVarName() : string;

    /**
     * Whether the record selection can require specific user rights.
     * This is used when displaying the empty selection screen,
     * to hint at the fact that rights may be missing.
     *
     * @return bool
     */
    public function isSelectionRightsBased() : bool;

    public function isRecordSelected(): bool;

    /**
     * Gets all records that may be selected
     * @return Application_CollectionItemInterface[]
     */
    public function getSelectableRecords() : array;

    /**
     * Gets the currently selected record, if any.
     *
     * @return Application_CollectionItemInterface|null
     */
    public function getRecord() : ?Application_CollectionItemInterface;

    /**
     * Gets the currently selected record, or throws an exception if none is selected.
     *
     * @return Application_CollectionItemInterface
     * @throws CollectionException
     */
    public function requireRecord() : Application_CollectionItemInterface;

    /**
     * Gets the URL with the current record selected,
     * or the base URL if none has been selected yet.
     *
     * @return AdminURLInterface
     */
    public function getURL() : AdminURLInterface;

    /**
     * Gets the URL with the specified record selected.
     *
     * @param Application_CollectionItemInterface $record
     * @return AdminURLInterface
     */
    public function getURLRecord(Application_CollectionItemInterface $record) : AdminURLInterface;

    /**
     * Optional abstract: If specified, the screen's abstract will
     * use this text, overriding any abstract already set by the
     * parent screen.
     *
     * @return string|null
     */
    public function getAbstract() : ?string;
}
