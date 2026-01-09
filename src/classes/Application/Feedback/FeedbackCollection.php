<?php

declare(strict_types=1);

namespace Application\Feedback;

use Application\Application;
use Application\Exception\UnexpectedInstanceException;
use Application_User;
use DateTime;
use DBHelper_BaseCollection;

class FeedbackCollection extends DBHelper_BaseCollection
{
    public const string TABLE_NAME = 'feedback';
    public const string PRIMARY_NAME = 'feedback_id';

    public const string SCOPE_PAGE = 'page';
    public const string SCOPE_APPLICATION = 'application';

    public const string TYPE_IMPROVEMENT = 'improvement';
    public const string TYPE_BUG = 'bug';
    public const string TYPE_OTHER = 'other';
    const string RECORD_TYPE = 'feedback_report';

    /**
     * Type ID => Human-readable label pairs.
     * @return array<string,string>
     */
    public function getTypes(): array
    {
        return array(
            self::TYPE_IMPROVEMENT => t('Improvement suggestion'),
            self::TYPE_BUG => t('Bug report'),
            self::TYPE_OTHER => t('Other')
        );
    }

    /**
     * @return string[]
     */
    public function getTypeIDs(): array
    {
        $types = $this->getTypes();
        return array_keys($types);
    }

    /**
     * Scope ID => Human-readable label pairs.
     * @return array<string,string>
     */
    public function getScopes(): array
    {
        return array(
            self::SCOPE_PAGE => t('A specific page'),
            self::SCOPE_APPLICATION => t('The entire application')
        );
    }

    /**
     * @return string[]
     */
    public function getScopeIDs(): array
    {
        $scopes = $this->getScopes();
        return array_keys($scopes);
    }

    public function getRecordClassName(): string
    {
        return FeedbackRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return FeedbackFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return FeedbackFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return 'date';
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            FeedbackRecord::COL_FEEDBACK => t('Feedback text')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE;
    }

    public function getCollectionLabel(): string
    {
        return t('Feedback');
    }

    public function getRecordLabel(): string
    {
        return t('Report');
    }

    public function addBug(string $scope, string $text, string $url = '', ?Application_User $user = null): FeedbackRecord
    {
        return $this->addFeedback(
            self::TYPE_BUG,
            $scope,
            $url,
            $text,
            $user
        );
    }

    public function addImprovement(string $scope, string $text, string $url = '', ?Application_User $user = null): FeedbackRecord
    {
        return $this->addFeedback(
            self::TYPE_IMPROVEMENT,
            $scope,
            $url,
            $text,
            $user
        );
    }

    /**
     * @param string $type The type of feedback, e.g. {@see FeedbackCollection::TYPE_IMPROVEMENT}.
     * @param string $scope The scope for the feedback, e.g. {@see FeedbackCollection::SCOPE_PAGE}.
     * @param string $url URL to the relevant page, if any
     * @param string $text
     * @param Application_User|null $user If other user than the logged-in user.
     * @return FeedbackRecord
     */
    public function addFeedback(string $type, string $scope, string $text, string $url = '', ?Application_User $user = null): FeedbackRecord
    {
        if ($user === null) {
            $user = Application::getUser();
        }

        $record = $this->createNewRecord(array(
            FeedbackRecord::COL_TYPE => $type,
            FeedbackRecord::COL_SCOPE => $scope,
            FeedbackRecord::COL_REQUEST_PARAMS => $url,
            FeedbackRecord::COL_DATE => new DateTime(),
            FeedbackRecord::COL_FEEDBACK => $text,
            FeedbackRecord::COL_USER_ID => $user->getID()
        ));

        if ($record instanceof FeedbackRecord) {
            return $record;
        }

        throw new UnexpectedInstanceException(FeedbackRecord::class, $record);
    }
}
