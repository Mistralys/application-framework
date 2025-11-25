<?php

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use Application\Revisionable\RevisionableInterface;
use DBHelper_BaseFilterCriteria;

/**
 * Base class for filter criteria to be used in conjunction
 * with revisionable record collections.
 *
 * @package Application
 * @subpackage Revisionable
 *
 * @property RevisionableCollectionInterface $collection
 */
abstract class BaseRevisionableFilterCriteria
    extends DBHelper_BaseFilterCriteria
    implements RevisionableFilterCriteriaInterface
{
    use RevisionableStateFilterTrait;

    // region: X - Interface methods

    public const string ALIAS_REVISION_TABLE = 'revs';
    public const string ALIAS_CURRENT_REVISION_TABLE = 'current_revs';

    protected string $primaryKeyName;
    protected string $revisionsTable;
    protected string $revisionKeyName;
    protected string $currentRevisionsTable;

    public function __construct(RevisionableCollectionFilteringInterface $collection)
    {
        parent::__construct($collection);

        $this->setSelectAlias(self::ALIAS_REVISION_TABLE);

        $this->primaryKeyName = $collection->getRecordPrimaryName();
        $this->revisionsTable = $collection->getRevisionsTableName();
        $this->currentRevisionsTable = $collection->getCurrentRevisionsTableName();
        $this->revisionKeyName = $collection->getRevisionKeyName();
    }

    protected function resolveTableFrom() : string
    {
        return sprintf(
            '`%s` AS `%s`',
            $this->revisionsTable,
            self::ALIAS_REVISION_TABLE
        );
    }

    protected function resolveTableSelector(): string
    {
        return $this->selectAlias ?? ('`' . $this->revisionsTable . '`');
    }

    protected function getSelect(): array
    {
        return array(
            sprintf("`%s`.`%s`", self::ALIAS_REVISION_TABLE, $this->primaryKeyName),
            sprintf("`%s`.`%s`", self::ALIAS_REVISION_TABLE, $this->revisionKeyName)
        );
    }

    public function getRevisionColumn(): string
    {
        return sprintf(
            '`%s`.`%s`',
            self::ALIAS_REVISION_TABLE,
            $this->revisionKeyName
        );
    }

    public function getStatusColumn(): string
    {
        return sprintf(
            '`%s`.`%s`',
            self::ALIAS_REVISION_TABLE,
            RevisionableCollectionInterface::COL_REV_STATE
        );
    }

    protected function getCountColumn(): string
    {
        return sprintf(
            '`%s`.`%s`',
            self::ALIAS_REVISION_TABLE,
            $this->revisionKeyName
        );
    }

    public function getSearchFields(): array
    {
        $keys = $this->collection->getRecordSearchableKeys();
        $result = array();
        foreach ($keys as $key) {
            $result[] = sprintf(
                '`%s`.`%s`',
                self::ALIAS_REVISION_TABLE,
                $key
            );
        }

        return $result;
    }

    /**
     * @return RevisionableInterface[]
     */
    public function getItemsObjects(): array
    {
        $result = array();

        foreach ($this->getIDs() as $id) {
            $result[] = $this->collection->getByID($id);
        }

        return $result;
    }

    /**
     * Retrieves all revisionable IDs for the current filters.
     * @return integer[]
     */
    public function getIDs(): array
    {
        $items = $this->getItems();
        $ids = array();
        foreach ($items as $item) {
            $ids[] = (int)$item[$this->primaryKeyName];
        }

        return $ids;
    }

    /**
     * Retrieves all revisionable revisions for the current filters.
     * These revisions are always the current revisions for the records.
     *
     * @return integer[]
     */
    public function getRevisions(): array
    {
        $revKey = $this->getRevisionColumn();
        $items = $this->getItems();
        $revs = array();
        foreach ($items as $item) {
            $revs[] = (int)$item[$revKey];
        }

        return $revs;
    }

    // endregion

    // region: Applying filters

    protected function prepareQuery(): void
    {
        $this->applyCurrentRevision();
        $this->applyIncludeStates();
        $this->applyExcludeStates();
        $this->applyCampaignKeys();
    }

    protected function applyCampaignKeys(): void
    {
        $campaignKeys = $this->collection->getCampaignKeys();
        foreach ($campaignKeys as $keyName => $keyValue) {
            $this->addWhere(sprintf(
                "`%s`.`%s`=:%s",
                self::ALIAS_REVISION_TABLE,
                $keyName,
                $keyName
            ));

            $this->addPlaceholder($keyName, $keyValue);
        }
    }

    protected function applyCurrentRevision(): void
    {
        $query = <<<'EOT'
LEFT JOIN
    `%1$s` AS `%2$s`
ON
`%3$s`.`%4$s`=`%2$s`.`%4$s`
EOT;

        $this->addJoin(
            sprintf(
                $query,
                $this->currentRevisionsTable,
                self::ALIAS_CURRENT_REVISION_TABLE,
                self::ALIAS_REVISION_TABLE,
                $this->primaryKeyName
            )
        );

        $this->addWhere(sprintf(
            "`%s`.`%s`=`%s`.`%s`",
            self::ALIAS_REVISION_TABLE,
            $this->revisionKeyName,
            self::ALIAS_CURRENT_REVISION_TABLE,
            RevisionableCollectionInterface::COL_CURRENT_REVISION
        ));
    }

    // endregion
}
