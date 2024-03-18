<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableStatelessInterface;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper\JSONConverter;
use TestDriver\Revisionables\RevisionableCollection;

abstract class Application_RevisionableCollection_DBRevisionable extends Application_Revisionable
{
    public const ERROR_NO_CURRENT_REVISION_FOUND = 14701;

    public const CHANGELOG_SET_LABEL = 'set_label';

    protected Application_RevisionableCollection $collection;
    protected int $id;
    protected int $currentRevision;

    public function __construct(Application_RevisionableCollection $collection, int $id)
    {
        $this->collection = $collection;
        $this->id = $id;

        parent::__construct();

        if ($this->isDummy()) {
            return;
        }

        $this->currentRevision = $this->collection->getCurrentRevision($id);

        if (!$this->currentRevision) {
            throw new Application_Exception(
                'Error loading current revision',
                sprintf(
                    'Could not load %s [%s] from database, no current revision found.',
                    $this->getRevisionableTypeName(),
                    $this->id
                ),
                self::ERROR_NO_CURRENT_REVISION_FOUND
            );
        }

        $this->selectCurrentRevision();
    }

    /**
     * Selects the revisionable's current revision.
     * @return $this
     */
    public function selectCurrentRevision(): self
    {
        return $this->selectRevision($this->currentRevision);
    }

    /**
     * Whether this is a stub object instance.
     * @return boolean
     * @deprecated Use {@see Application_Revisionable::isStub()} instead.
     */
    public function isDummy(): bool
    {
        return $this->isStub();
    }

    /**
     * Retrieves the revisionable's collection instance.
     * @return Application_RevisionableCollection
     */
    public function getCollection(): Application_RevisionableCollection
    {
        return $this->collection;
    }

    /**
     * @throws RevisionableException
     * @see BaseDBCollectionStorage
     */
    protected function createRevisionStorage(): BaseDBCollectionStorage
    {
        try {
            $className = $this->collection->getRevisionsStorageClass();

            return ClassHelper::requireObjectInstanceOf(
                BaseDBCollectionStorage::class,
                new $className($this)
            );
        } catch (BaseClassHelperException $e) {
            throw new RevisionableException(
                'Invalid revision storage',
                sprintf(
                    'The revision storage for [%s] must extend the base [%s] class.',
                    get_class($this),
                    'BaseDBCollectionStorage'
                ),
                RevisionableStatelessInterface::ERROR_INVALID_REVISION_STORAGE,
                $e
            );
        }
    }

    public function getID(): int
    {
        return $this->id;
    }

    protected function _saveWithStateChange(): void
    {
        $this->revisions->writeRevisionKeys(array(
            Application_RevisionableCollection::COL_REV_STATE => $this->getStateName()
        ));
    }

    protected function _save(): void
    {
    }

    /**
     * Saves the current values of the specified data keys to
     * the revision table for the current revision.
     *
     * @param string[] $columnNames
     * @deprecated Not used anymore.
     */
    protected function saveRevisionData(array $columnNames): void
    {

    }

    /**
     * Retrieves the base URL parameters collection used to
     * administrate this revisionable. Presupposes that an
     * administration interface exists for it.
     *
     * @return array
     */
    public function getAdminURLParams(): array
    {
        $params = $this->collection->getAdminURLParams();
        $params[$this->collection->getPrimaryKeyName()] = $this->getID();
        return $params;
    }

    protected function getAdminURL(array $params = array()): string
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }

    protected bool $handleDBTransaction = false;

    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null): self
    {
        // to allow this transaction to be wrapped in an 
        // existing transaction, we check if we have to 
        // start one automatically or not.
        $this->handleDBTransaction = false;

        if (!DBHelper::isTransactionStarted()) {
            $this->handleDBTransaction = true;
            DBHelper::startTransaction();
        }

        return parent::startTransaction($newOwnerID, $newOwnerName, $comments);
    }

    public function endTransaction(): bool
    {
        $this->save();

        // avoid creating a new revision if the structure has not been changed.
        if (!$this->hasStructuralChanges()) {
            $this->log('No structural changes made, no new revision will be created.');
            $this->requiresNewRevision = false;
        }

        // we need to do this, because we want to trigger it later
        $this->ignoreEvent('TransactionEnded');

        $result = parent::endTransaction();

        // now make sure the current revision is set correctly, regardless
        // of whether we added a new revision or not.
        $this->collection->setCurrentRevision($this->id, $this->getRevision());

        // do we handle the DB transaction here? 
        if ($this->handleDBTransaction) {
            if ($this->simulation) {
                $this->log('Simulation mode, transaction will not be committed.');
                DBHelper::rollbackTransaction();
                return $result;
            }

            DBHelper::commitTransaction();
        }

        $this->log('Reloading the revision data.');
        $this->revisions->reload();

        // now that everything's through, we can trigger the event.
        $this->unignoreEvent('TransactionEnded');
        $this->triggerEvent('TransactionEnded');

        $this->log('Comments: ' . $this->getRevisionComments());

        return $result;
    }

    /**
     * @return $this
     * @throws DBHelper_Exception
     */
    public function rollBackTransaction(): self
    {
        parent::rollBackTransaction();

        DBHelper::rollbackTransaction();

        return $this;
    }

    public function getChangelogTable(): string
    {
        return $this->collection->getRecordChangelogTableName();
    }

    public function getChangelogItemPrimary(): array
    {
        return array(
            $this->collection->getPrimaryKeyName() => $this->getID(),
            $this->collection->getRevisionKeyName() => $this->getRevision()
        );
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    abstract public function getAdminStatusURL(array $params = array()): string;

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    abstract public function getAdminChangelogURL(array $params = array()): string;

    /**
     * Selects the last revision of the record by a specific state.
     *
     * @param Application_StateHandler_State $state
     * @return integer|boolean The revision number, or false if no revision matches.
     */
    public function selectLastRevisionByState(Application_StateHandler_State $state)
    {
        $revision = $this->getLastRevisionByState($state);
        if ($revision) {
            $this->selectRevision($revision);
            return $revision;
        }

        return false;
    }

    /**
     * Retrieves the last revision of the record by a specific state.
     *
     * @param Application_StateHandler_State $state
     * @return integer|boolean The revision number, or false if no revision matches.
     */
    public function getLastRevisionByState(Application_StateHandler_State $state)
    {
        $revisionKey = $this->collection->getRevisionKeyName();
        $primaryKey = $this->collection->getPrimaryKeyName();

        $where = $this->collection->getCampaignKeys();
        $where[$primaryKey] = $this->getID();
        $where[Application_RevisionableCollection::COL_REV_STATE] = $state->getName();

        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s
            ORDER BY
                `date` DESC
            LIMIT 0,1",
            $revisionKey,
            $this->collection->getRevisionsTableName(),
            DBHelper::buildWhereFieldsStatement($where)
        );

        $revision = DBHelper::fetchKeyInt($revisionKey, $query, $where);

        if (!empty($revision)) {
            return $revision;
        }

        return false;
    }

    /**
     * Retrieves the revision currently in use. This is tracked in
     * a dedicated table, and namespaced to any campaign keys that
     * may have been defined.
     *
     * @return integer|NULL
     */
    public function getCurrentRevision(): ?int
    {
        return $this->collection->getCurrentRevision($this->getID());
    }

    public function getPrettyRevision(): int
    {
        return (int)$this->revisions->getKey(Application_RevisionableCollection::COL_REV_PRETTY_REVISION);
    }

    public function getLabel(): string
    {
        return (string)$this->getRevisionKey(Application_RevisionableCollection::COL_REV_LABEL);
    }

    public function setLabel(string $label): self
    {
        $this->setCustomKey(
            Application_RevisionableCollection::COL_REV_LABEL,
            $label,
            false,
            self::CHANGELOG_SET_LABEL
        );

        return $this;
    }
}
