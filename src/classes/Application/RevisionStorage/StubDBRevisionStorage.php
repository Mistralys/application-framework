<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\RevisionStorage;

use Application;
use Application_RevisionableStateless;
use Application_StateHandler_State;
use Application_User;
use BaseDBCollectionStorage;
use DateTime;

/**
 * Revision storage used by revisionable stub objects.
 * It does not use the database, but accepts adding and
 * loading virtual revisions.
 *
 * @package Application
 * @subpackage Revisionables
 */
class StubDBRevisionStorage extends BaseDBCollectionStorage
{
    public const ERROR_CANNOT_CREATE_STUB_REVISION = 153501;
    public const STUB_REVISION_NUMBER = 1;

    public function __construct(Application_RevisionableStateless $revisionable)
    {
        parent::__construct($revisionable);

        $user = Application::createSystemUser();

        $this->addRevision(
            self::STUB_REVISION_NUMBER,
            $user->getID(),
            $user->getName()
        );
    }

    public function createRevision(int $revisionable_id, string $label, Application_StateHandler_State $state, DateTime $date, ?Application_User $author = null, int $prettyRevision = 1, ?string $comments = null, array $customColumns = array()): int
    {
        throw new RevisionStorageException(
            'Cannot create a revision for a stub object',
            '',
            self::ERROR_CANNOT_CREATE_STUB_REVISION
        );
    }

    public function getNextRevisionData(): array
    {
        return array();
    }

    protected function _loadRevision(int $number): void
    {
        $author = Application::createSystemUser();

        $this->addRevision(
            $number,
            $author->getID(),
            $author->getName()
        );
    }

    public function countRevisions(): int
    {
        return count($this->data);
    }

    public function revisionExists(int $number, bool $forceLiveCheck = false): bool
    {
        return isset($this->data[$number]);
    }

    public function nextRevision(int $ownerID, string $ownerName, ?string $comments): int
    {
        return max(array_keys($this->data)) + 1;
    }

    public function nextPrettyRevision(): int
    {
        return 1;
    }

    protected function _removeRevision(int $number): self
    {
        unset($this->data[$number]);
        return $this;
    }

    public function getRevisions(): array
    {
        return array_keys($this->data);
    }

    protected function _loadDataKey(string $name): string
    {
        return '';
    }

    protected function _writeDataKey(string $key, $value): void
    {
    }

    protected function _writeRevisionKeys(array $data): void
    {

    }
}
