<?php

declare(strict_types=1);

use Application\Exception\UnexpectedInstanceException;
use AppUtils\Microtime;

class Application_Feedback extends DBHelper_BaseCollection
{
    const TABLE_NAME = 'feedback';
    const PRIMARY_NAME = 'feedback_id';

    const SCOPE_PAGE = 'page';
    const SCOPE_APPLICATION = 'application';

    const TYPE_IMPROVEMENT = 'improvement';
    const TYPE_BUG = 'bug';
    const TYPE_OTHER = 'other';

    /**
     * Type ID => Human-readable label pairs.
     * @return array<string,string>
     */
    public function getTypes() : array
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
    public function getTypeIDs() : array
    {
        $types = $this->getTypes();
        return array_keys($types);
    }

    /**
     * Scope ID => Human-readable label pairs.
     * @return array<string,string>
     */
    public function getScopes() : array
    {
        return array(
            self::SCOPE_PAGE => t('A specific page'),
            self::SCOPE_APPLICATION => t('The entire application')
        );
    }

    /**
     * @return string[]
     */
    public function getScopeIDs() : array
    {
        $scopes = $this->getScopes();
        return array_keys($scopes);
    }

    public function getRecordClassName() : string
    {
        return Application_Feedback_Report::class;
    }

    public function getRecordFiltersClassName() : string
    {
        return '';
    }

    public function getRecordFilterSettingsClassName() : string
    {
        return '';
    }

    public function getRecordDefaultSortKey() : string
    {
        return 'date';
    }

    public function getRecordSearchableColumns() : array
    {
        return array(
            Application_Feedback_Report::COL_FEEDBACK => t('Feedback text')
        );
    }

    public function getRecordTableName() : string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName() : string
    {
        return 'feedback_report';
    }

    public function getCollectionLabel() : string
    {
        return t('Feedback');
    }

    public function getRecordLabel() : string
    {
        return t('Report');
    }

    public function getRecordProperties() : array
    {
        return array();
    }

    public function addBug(string $scope, string $text, string $url='', ?Application_User $user=null) : Application_Feedback_Report
    {
        return $this->addFeedback(
            self::TYPE_BUG,
            $scope,
            $url,
            $text,
            $user
        );
    }

    public function addImprovement(string $scope, string $text, string $url='', ?Application_User $user=null) : Application_Feedback_Report
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
     * @param string $type The type of feedback, e.g. {@see Application_Feedback::TYPE_IMPROVEMENT}.
     * @param string $scope The scope for the feedback, e.g. {@see Application_Feedback::SCOPE_PAGE}.
     * @param string $url URL to the relevant page, if any
     * @param string $text
     * @param Application_User|null $user If other user than the logged-in user.
     * @return Application_Feedback_Report
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws UnexpectedInstanceException
     * @throws DBHelper_Exception
     */
    public function addFeedback(string $type, string $scope,string $text, string $url='', ?Application_User $user=null) : Application_Feedback_Report
    {
        if($user === null)
        {
            $user = Application::getUser();
        }

        $record = $this->createNewRecord(array(
            Application_Feedback_Report::COL_TYPE => $type,
            Application_Feedback_Report::COL_SCOPE => $scope,
            Application_Feedback_Report::COL_REQUEST_PARAMS => $url,
            Application_Feedback_Report::COL_DATE => new DateTime(),
            Application_Feedback_Report::COL_FEEDBACK => $text,
            Application_Feedback_Report::COL_USER_ID => $user->getID()
        ));

        if($record instanceof Application_Feedback_Report)
        {
            return $record;
        }

        throw new UnexpectedInstanceException(Application_Feedback_Report::class, $record);
    }
}
