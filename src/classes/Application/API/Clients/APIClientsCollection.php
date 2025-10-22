<?php
/**
 * @package API
 * @subpackage Clients
 */

declare(strict_types=1);

namespace Application\API\Clients;

use Application\API\Admin\APICollectionURLs;
use Application\AppFactory;
use Application_User;
use Application_Users_User;
use AppUtils\RegexHelper;
use DBHelper_BaseCollection;

/**
 * @package API
 * @subpackage Clients
 *
 * @method APIClientFilterCriteria getFilterCriteria()
 * @method APIClientFilterSettings getFilterSettings()
 * @method APIClientRecord[] getAll()
 * @method APIClientRecord getByID($record_id)
 * @method APIClientRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class APIClientsCollection extends DBHelper_BaseCollection
{
    public const string TABLE_NAME = 'api_clients';
    public const string PRIMARY_NAME = 'api_client_id';
    public const string RECORD_TYPE_NAME = 'api_client';

    public const string COL_LABEL = 'label';
    public const string COL_FOREIGN_ID = 'foreign_id';
    public const string COL_DATE_CREATED = 'date_created';
    public const string COL_CREATED_BY = 'created_by';
    public const string COL_IS_ACTIVE = 'is_active';
    public const string COL_COMMENTS = 'comments';

    // region: Collection core

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired()
            ->setRegexValidation(RegexHelper::REGEX_NAME_OR_TITLE);

        $this->keys->register(self::COL_FOREIGN_ID)
            ->makeRequired()
            ->setRegexValidation(RegexHelper::REGEX_ALIAS_CAPITALS);

        $this->keys->register(self::COL_DATE_CREATED)
            ->makeRequired()
            ->setMicrotimeGenerator();

        $this->keys->register(self::COL_CREATED_BY)
            ->makeRequired()
            ->setCurrentUserGenerator();

        $this->keys->register(self::COL_IS_ACTIVE)
            ->setEnumValidation(array('yes', 'no'));
    }

    public function getRecordClassName(): string
    {
        return APIClientRecord::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return APIClientFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return APIClientFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label'),
            self::COL_FOREIGN_ID => t('Foreign ID'),
            self::COL_COMMENTS => t('Comments')
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
        return self::RECORD_TYPE_NAME;
    }

    public function getCollectionLabel(): string
    {
        return t('API Clients');
    }

    public function getRecordLabel(): string
    {
        return t('API Client');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    // endregion

    // region: Custom methods

    public function createNewClient(
        string $label,
        string $foreignID,
        ?string $comments = null,
        int|Application_User|Application_Users_User|null $createdBy = null,
    ) : APIClientRecord
    {
        if($createdBy === null) {
            $userID = AppFactory::createUser()->getID();
        } elseif($createdBy instanceof Application_User || $createdBy instanceof Application_Users_User) {
            $userID = $createdBy->getID();
        } else {
            $userID = $createdBy;
        }

        return $this->createNewRecord(array(
            self::COL_LABEL => $label,
            self::COL_FOREIGN_ID => $foreignID,
            self::COL_CREATED_BY => $userID,
            self::COL_IS_ACTIVE => 'yes',
            self::COL_COMMENTS => (string)$comments
        ));
    }

    public function adminURL() : APICollectionURLs
    {
        return new APICollectionURLs();
    }

    // endregion
}
