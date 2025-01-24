<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

use Application\Exception\DisposableDisposedException;
use Application\Revisionable\Changelog\RevisionableChangelogHandlerInterface;
use Application\Revisionable\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\RevisionStorage\RevisionStorageException;
use Application\RevisionStorage\StubDBRevisionStorage;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;

/**
 * @package Application
 * @subpackage Revisionables
 *
 * @property BaseDBCollectionStorage $revisions
 */
abstract class Application_RevisionableCollection_DBRevisionable
    extends Application_Revisionable
{
    public const ERROR_NO_CURRENT_REVISION_FOUND = 14701;
    public const ERROR_LAST_TRANSACTION_NOT_AVAILABLE = 14702;


    protected Application_RevisionableCollection $collection;
    protected int $id;

    public function __construct(Application_RevisionableCollection $collection, int $id)
    {
        $this->collection = $collection;
        $this->id = $id;

        parent::__construct();

        $this->selectCurrentRevision();
    }

    /**
     * Selects the revisionable's current revision.
     * @return $this
     *
     * @throws DisposableDisposedException
     * @throws RevisionableException
     * @throws RevisionStorageException
     */
    public function selectCurrentRevision(): self
    {
        $current = $this->getCurrentRevision();

        if ($current !== null) {
            return $this->selectRevision($current);
        }

        throw new RevisionableException(
            'Error selecting current revision',
            sprintf(
                'Could not load %s [%s] from database, no current revision found.',
                $this->getRevisionableTypeName(),
                $this->id
            ),
            self::ERROR_NO_CURRENT_REVISION_FOUND
        );
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
        if($this->isStub()) {
            return new StubDBRevisionStorage($this);
        }

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
            RevisionableCollectionInterface::COL_REV_STATE => $this->getStateName()
        ));
    }

    protected function _saveWithoutStateChange() : void
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
        // we need to do this, because we want to trigger it later
        $this->ignoreEvent(self::EVENT_TRANSACTION_ENDED);

        $result = parent::endTransaction();

        if($result) {
            $this->collection->setCurrentRevision($this->id, $this->getRevision());
        }

        // do we handle the DB transaction here? 
        if ($this->handleDBTransaction) {
            if ($this->isSimulationEnabled()) {
                $this->log('Transaction | END | Simulation mode, transaction will not be committed.');
                DBHelper::rollbackTransaction();
            } else {
                $this->log('Transaction | END | Committing transaction.');
                DBHelper::commitTransaction();
            }
        }

        // now that everything's through, we can trigger the event.
        $this->unIgnoreEvent(self::EVENT_TRANSACTION_ENDED);

        if(isset($this->lastTransaction)) {
            $this->triggerTransactionEnded($this->lastTransaction);
            return $result;
        }

        throw new RevisionableException(
            'No last transaction data stored.',
            'The last transaction data is not available, and the transaction cannot be completed.',
            self::ERROR_LAST_TRANSACTION_NOT_AVAILABLE
        );
    }

    /**
     * @return $this
     * @throws DBHelper_Exception
     */
    public function rollBackTransaction(): self
    {
        parent::rollBackTransaction();

        if($this->handleDBTransaction) {
            DBHelper::rollbackTransaction();
        }

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

    public function getChangelogFilterSelects(): array
    {
        return array(
            $this->collection->getPrimaryKeyName() => (string)$this->getID()
        );
    }

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
        $where[RevisionableCollectionInterface::COL_REV_STATE] = $state->getName();

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
        if($this->isStub()) {
            return StubDBRevisionStorage::STUB_REVISION_NUMBER;
        }

        return $this->collection->getCurrentRevision($this->getID());
    }

    public function getPrettyRevision(): int
    {
        return (int)$this->revisions->getKey(RevisionableCollectionInterface::COL_REV_PRETTY_REVISION);
    }

    public function getLabel(): string
    {
        return (string)$this->getRevisionKey(RevisionableCollectionInterface::COL_REV_LABEL);
    }

    public function setLabel(string $label): self
    {
        $this->setCustomKey(
            RevisionableCollectionInterface::COL_REV_LABEL,
            $label,
            false,
            RevisionableChangelogHandlerInterface::CHANGELOG_SET_LABEL
        );

        return $this;
    }
}
