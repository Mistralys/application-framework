<?php
/**
 * @package API
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\Admin\APIKeyCollectionURLs;
use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use AppUtils\Microtime;
use DBHelper\BaseCollection\BaseChildCollection;
use DBHelper_BaseRecord;

/**
 * API Keys Collection that handles the available keys for an API client.
 *
 * @package API
 * @subpackage API Keys
 *
 * @method APIClientsCollection getParentCollection()
 * @method APIClientRecord getParentRecord()
 * @method APIKeyFilterCriteria getFilterCriteria()
 * @method APIKeyFilterSettings getFilterSettings()
 * @method APIKeyRecord[] getAll()
 * @method APIKeyRecord getByID($record_id)
 * @method APIKeyRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class APIKeysCollection extends BaseChildCollection
{
    public const string TABLE_NAME = 'api_keys';
    public const string PRIMARY_NAME = 'api_key_id';

    public const string COL_API_CLIENT_ID = 'api_client_id';
    public const string COL_API_KEY = 'api_key';
    public const string COL_LABEL = 'label';
    public const string COL_COMMENTS = 'comments';
    public const string COL_GRANT_ALL_METHODS = 'grant_all_methods';
    public const string COL_DATE_CREATED = 'date_created';
    public const string COL_CREATED_BY = 'created_by';
    public const string COL_EXPIRY_DATE = 'expiry_date';
    public const string COL_EXPIRY_DELAY = 'expiry_delay';
    public const string COL_EXPIRED = 'expired';
    public const string COL_LAST_USED = 'last_used';
    public const string COL_USAGE_COUNT = 'usage_count';

    // region: Collection core

    public const string RECORD_TYPE_NAME = 'api_key';

    public function getRecordClassName(): string
    {
        return APIKeyRecord::class;
    }

    public function getParentCollectionClass(): string
    {
        return APIClientsCollection::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return APIKeyFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return APIKeyFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label'),
            self::COL_API_KEY => t('API Key'),
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
        return t('API Keys');
    }

    public function getRecordLabel(): string
    {
        return t('API Key');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    // endregion

    public function generateKey() : string
    {
        return bin2hex(random_bytes(24));
    }

    public function createNewAPIKey(string $label) : APIKeyRecord
    {
        return $this->createNewRecord(array(
            self::COL_API_KEY => $this->generateKey(),
            self::COL_LABEL => $label,
            self::COL_DATE_CREATED => Microtime::createNow(),
            self::COL_CREATED_BY => AppFactory::createUser()->getID(),
        ));
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_API_KEY)
            ->setGenerator(function () : string {
                return $this->generateKey();
            })
            ->makeRequired();

        $this->keys->register(self::COL_DATE_CREATED)
            ->makeRequired()
            ->setMicrotimeGenerator();

        $this->keys->register(self::COL_CREATED_BY)
            ->makeRequired()
            ->setCurrentUserGenerator();
    }

    private ?APIKeyCollectionURLs $adminURLs = null;

    public function adminURLs() : APIKeyCollectionURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new APIKeyCollectionURLs($this);
        }

        return $this->adminURLs;
    }
}
