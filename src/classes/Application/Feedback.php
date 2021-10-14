<?php

declare(strict_types=1);

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

    public function getRecordClassName()
    {
        return Application_Feedback_Report::class;
    }

    public function getRecordFiltersClassName()
    {
        return '';
    }

    public function getRecordFilterSettingsClassName()
    {
        return '';
    }

    public function getRecordDefaultSortKey()
    {
        return 'date';
    }

    public function getRecordSearchableColumns()
    {
        return array(
            Application_Feedback_Report::COL_FEEDBACK => t('Feedback text')
        );
    }

    public function getRecordTableName()
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName()
    {
        return self::PRIMARY_NAME;
    }

    public function getRecordTypeName()
    {
        return 'feedback_report';
    }

    public function getCollectionLabel()
    {
        return t('Feedback');
    }

    public function getRecordLabel()
    {
        return t('Report');
    }

    public function getRecordProperties()
    {
        return array();
    }

    /**
     * @param string $type
     * @param string $scope
     * @param string $url
     * @param string $text
     * @return Application_Feedback_Report|DBHelper_BaseRecord
     * @throws Application_Exception
     * @throws Application_Exception_DisposableDisposed
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception
     */
    public function addFeedback(string $type, string $scope, string $url, string $text)
    {
        $record = $this->createNewRecord(array(
            Application_Feedback_Report::COL_TYPE => $type,
            Application_Feedback_Report::COL_SCOPE => $scope,
            Application_Feedback_Report::COL_REQUEST_PARAMS => $url,
            Application_Feedback_Report::COL_DATE => new DateTime(),
            Application_Feedback_Report::COL_FEEDBACK => $text,
            Application_Feedback_Report::COL_USER_ID => Application::getUser()->getID()
        ));

        if($record instanceof Application_Feedback_Report)
        {
            return $record;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Feedback_Report::class, $record);
    }
}
