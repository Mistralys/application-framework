<?php
/**
 * @package TestDriver
 * @supackage Collection
 */

declare(strict_types=1);

namespace TestDriver\Collection\Admin;

use Application\Collection\Admin\BaseRecordSelectionTieIn;
use Application\Collection\CollectionItemInterface;
use TestDriver\Collection\MythologyRecordCollection;
use UI_Bootstrap_BigSelection_Item_Regular;

/**
 * Admin screen tie-in for selecting a collection record
 * that uses strings as primary key values.
 *
 * @package TestDriver
 * @supackage Collection
 */
class MythologicalRecordSelectionTieIn extends BaseRecordSelectionTieIn
{
    public const HIDDEN_VAR_NAME = 'mythology-var';
    public const HIDDEN_VAR_VALUE = 'mythology-value';
    public const HIDDEN_VAR_EMPTY_NAME = 'empty-mythology-var';

    private MythologyRecordCollection $collection;

    protected function init(): void
    {
        $this->collection = MythologyRecordCollection::getInstance();
    }

    protected function recordIDExists($id): bool
    {
        return $this->collection->idExists((string)$id);
    }

    public function _getHiddenVars(): array
    {
        return array(
            self::HIDDEN_VAR_NAME => self::HIDDEN_VAR_VALUE,
            self::HIDDEN_VAR_EMPTY_NAME => ''
        );
    }

    protected function getRecordByID($id): CollectionItemInterface
    {
        return $this->collection->getByID((string)$id);
    }

    protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, CollectionItemInterface $record): void
    {

    }

    public function getRequestPrimaryVarName(): string
    {
        return MythologyRecordCollection::REQUEST_VAR_NAME;
    }

    public function isSelectionRightsBased(): bool
    {
        return false;
    }

    public function getSelectableRecords(): array
    {
        return $this->collection->getAll();
    }

    public function getAbstract(): ?string
    {
        return t('Please select a mythological figure.');
    }
}
