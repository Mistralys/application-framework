<?php
/**
 * @package API
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\Admin\APIKeyURLs;
use Application\API\Admin\APIScreenRights;
use Application\API\Clients\APIClientRecord;
use Application\AppFactory;
use Application_Users_User;
use AppUtils\ClassHelper;
use AppUtils\DateTimeHelper\DateIntervalExtended;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Microtime;
use DBHelper_BaseRecord;

/**
 * @package API
 * @subpackage API Keys
 *
 * @method APIKeysCollection getCollection()
 */
class APIKeyRecord extends DBHelper_BaseRecord
{
    public function getClientID() : int
    {
        return $this->getRecordIntKey(APIKeysCollection::COL_API_CLIENT_ID);
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(APIKeysCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()->linkRight(
            $this->getLabel(),
            $this->adminURL()->status(),
            APIScreenRights::SCREEN_API_KEYS_STATUS
        );
    }

    public function setLabel(string|StringableInterface $label) : self
    {
        $this->setRecordKey(APIKeysCollection::COL_LABEL, (string)$label);
        return $this;
    }

    public function getDateCreated() : Microtime
    {
        return $this->requireRecordMicrotimeKey(APIKeysCollection::COL_DATE_CREATED);
    }

    public function getCreatedByID() : int
    {
        return $this->getRecordIntKey(APIKeysCollection::COL_CREATED_BY);
    }

    public function getCreatedBy() : Application_Users_User
    {
        return AppFactory::createUsers()->getByID($this->getCreatedByID());
    }

    public function isExpired() : bool
    {
        return $this->getRecordBooleanKey(APIKeysCollection::COL_EXPIRED);
    }

    public function areAllMethodsGranted() : bool
    {
        return $this->getRecordBooleanKey(APIKeysCollection::COL_GRANT_ALL_METHODS);
    }

    public function setGrantAll(bool $grant) : self
    {
        $this->setRecordBooleanKey(APIKeysCollection::COL_GRANT_ALL_METHODS, $grant);
        return $this;
    }

    private ?APIKeyMethods $methods = null;

    /**
     * Gets the API method manager for this API key,
     * which can be used to manage which methods are
     * granted to this key.
     *
     * @return APIKeyMethods
     */
    public function getMethods() : APIKeyMethods
    {
        if(!isset($this->methods)) {
            $this->methods = new APIKeyMethods($this);
        }

        return $this->methods;
    }

    public function updateLastUsed() : self
    {
        $this->setRecordDateKey(APIKeysCollection::COL_LAST_USED, Microtime::createNow());

        $this->setRecordKey(
            APIKeysCollection::COL_USAGE_COUNT,
            $this->getUsageCount() + 1
        );

        return $this->saveChained();
    }

    public function getLastUsed() : ?Microtime
    {
        return $this->getRecordMicrotimeKey(APIKeysCollection::COL_LAST_USED);
    }

    public function getUsageCount() : int
    {
        return $this->getRecordIntKey(APIKeysCollection::COL_USAGE_COUNT);
    }

    public function getLastUsedDate() : ?Microtime
    {
        return $this->getRecordMicrotimeKey(APIKeysCollection::COL_LAST_USED);
    }

    public function getExpiryDate() : ?Microtime
    {
        return $this->getRecordMicrotimeKey(APIKeysCollection::COL_EXPIRY_DATE);
    }

    public function resolveExpiryDate() : ?Microtime
    {
        $date = $this->getExpiryDate();
        if($date !== null) {
            return $date;
        }

        $delay = $this->getExpiryDelay();
        if($delay === null) {
            return null;
        }

        return $this->getDateCreated()->add($delay->getInterval());
    }

    public function getExpiryDelay() : ?DateIntervalExtended
    {
        $delayText = $this->getRecordStringKey(APIKeysCollection::COL_EXPIRY_DELAY);
        if(empty($delayText)) {
            return null;
        }

        return DateIntervalExtended::fromDurationString($delayText);
    }

    public function getAPIKey() : string
    {
        return $this->getRecordStringKey(APIKeysCollection::COL_API_KEY);
    }

    public function getClient() : APIClientRecord
    {
        return $this->getParentRecord();
    }

    /**
     * @return APIClientRecord
     */
    public function getParentRecord(): DBHelper_BaseRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            APIClientRecord::class,
            parent::getParentRecord()
        );
    }

    private ?APIKeyURLs $adminURLs = null;

    public function adminURL() : APIKeyURLs
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new APIKeyURLs($this);
        }

        return $this->adminURLs;
    }
}
