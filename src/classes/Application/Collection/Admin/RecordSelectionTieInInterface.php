<?php
/**
 * @package Application
 * @subpackage Collections
 */

declare(strict_types=1);

namespace Application\Collection\Admin;

use Application\Collection\CollectionException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Collection\CollectionItemInterface;
use Application\Interfaces\HiddenVariablesInterface;
use AppUtils\Interfaces\StringableInterface;
use Closure;
use UI\AdminURLs\AdminURLInterface;

/**
 * Interface for a record selection tie-in.
 *
 * See the base implementation in {@see BaseRecordSelectionTieIn}
 * for details and usage instructions.
 *
 * @package Application
 * @subpackage Collections
 */
interface RecordSelectionTieInInterface
{
    public const COMPACT_LIST_THRESHOLD = 10;

    public function getScreen(): AdminScreenInterface;

    /**
     * Gets the name of the primary request variable that
     * is used to select the record.
     *
     * @return string
     */
    public function getRequestPrimaryVarName(): string;

    /**
     * Gets all hidden variables required for the
     * record selection and the current admin screen.
     *
     * > NOTE: Empty values are pruned from the result,
     * > and the variables are sorted alphabetically by
     * > key for consistency.
     *
     * @return array<string,string|int|StringableInterface|NULL>
     */
    public function getHiddenVars() : array;

    /**
     * Whether the record selection can require specific user rights.
     * This is used when displaying the empty selection screen,
     * to hint at the fact that rights may be missing.
     *
     * @return bool
     */
    public function isSelectionRightsBased(): bool;

    public function isRecordSelected(): bool;

    /**
     * Gets all records that may be selected
     * @return CollectionItemInterface[]
     */
    public function getSelectableRecords(): array;

    /**
     * Gets the currently selected record, if any.
     *
     * @return CollectionItemInterface|null
     */
    public function getRecord(): ?CollectionItemInterface;

    /**
     * @return string|int|NULL
     */
    public function getRecordID();

    /**
     * Injects all hidden variables required to select the current record, if any.
     *
     * @param HiddenVariablesInterface $subject
     * @return $this
     */
    public function injectHiddenVars(HiddenVariablesInterface $subject) : self;

    /**
     * Gets the currently selected record, or throws an exception if none is selected.
     *
     * @return CollectionItemInterface
     * @throws CollectionException
     */
    public function requireRecord(): CollectionItemInterface;

    /**
     * Gets the URL with the current record selected,
     * or the base URL if none has been selected yet.
     *
     * @return AdminURLInterface
     */
    public function getURL(): AdminURLInterface;

    /**
     * Gets the URL with the specified record selected.
     *
     * @param CollectionItemInterface $record
     * @return AdminURLInterface
     */
    public function getURLRecord(CollectionItemInterface $record): AdminURLInterface;

    /**
     * Optional abstract: If specified, the screen's abstract will
     * use this text, overriding any abstract already set by the
     * parent screen.
     *
     * @return string|null
     */
    public function getAbstract(): ?string;

    /**
     * Whether the record selection is enabled.
     * By default, this is enabled if no record is selected yet.
     *
     * @return bool
     * @see self::getEnabledCallback()
     */
    public function isEnabled(): bool;

    /**
     * Custom logic callback to determine if the record selection
     * should be enabled.
     *
     * @return Closure|null
     */
    public function getEnabledCallback(): ?Closure;

    /**
     * Optional custom logic callback to determine if the record
     * selection should be enabled.
     *
     * By default, it is enabled automatically if no record is
     * selected yet.
     *
     * > NOTE: This overrides the default behavior.
     *
     * @param Closure|null $callback The closure must return a boolean value.
     * @return $this
     */
    public function setEnabledCallback(?Closure $callback): self;

    /**
     * Adds a request variable that should be inherited in the
     * screen's URL if it is present in the request.
     *
     * @param string $name
     * @return $this
     */
    public function inheritRequestVar(string $name): self;

    /**
     * Gets the ancestry of this tie-in, starting with the topmost
     * parent and ending with this tie-in.
     *
     * @return RecordSelectionTieInInterface[]
     */
    public function getAncestry() : array;

    /**
     * Gets the parent tie-in, if any, from which this tie-in's
     * record selection and enabled status is inherited.
     *
     * @return RecordSelectionTieInInterface|NULL
     */
    public function getParent() : ?RecordSelectionTieInInterface;
}
