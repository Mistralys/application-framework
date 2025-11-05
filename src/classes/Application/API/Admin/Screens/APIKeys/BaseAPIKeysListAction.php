<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\Microtime;
use DBHelper\Admin\Screens\Action\BaseRecordListAction;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI\AdminURLs\AdminURLInterface;

class BaseAPIKeysListAction extends BaseRecordListAction implements APIClientRequestInterface
{
    use APIClientRequestTrait;

    public const string URL_NAME = 'list';

    public const string COL_LABEL = 'label';
    public const string COL_METHOD_COUNT = 'method_count';
    public const string COL_LAST_ACCESSED = 'last_accessed';
    public const string COL_USER = 'user';
    public const string COL_CREATED = 'created';
    public const string COL_EXPIRES = 'expires';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Overview of API Keys');
    }

    /**
     * @return APIKeysCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return $this->getAPIClientRequest()->getRecordOrRedirect()->createAPIKeys();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $key = $this->resolveRecord($record);

        return array(
            self::COL_LABEL => $key->getLabelLinked(),
            self::COL_USER => $key->getPseudoUser()->getName(),
            self::COL_METHOD_COUNT => $key->getMethods()->countMethods(),
            self::COL_LAST_ACCESSED => $this->renderDate($key->getLastUsedDate()),
            self::COL_CREATED => $this->renderDate($key->getDateCreated()),
            self::COL_EXPIRES => $this->renderDate($key->getExpiryDate(), t('Never'))
        );
    }

    private function resolveRecord(DBHelper_BaseRecord $record) : APIKeyRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            $record
        );
    }

    private function renderDate(?Microtime $date, ?string $emptyMessage=null) : string
    {
        if(empty($emptyMessage)) {
            $emptyMessage = t('Never accessed');
        }

        if($date === null) {
            return (string)sb()->muted($emptyMessage);
        }

        return ConvertHelper::date2listLabel($date, true, true);
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_LABEL, t('Label'));
        $this->grid->addColumn(self::COL_USER, t('User'));
        $this->grid->addColumn(self::COL_METHOD_COUNT, t('Granted methods'));
        $this->grid->addColumn(self::COL_LAST_ACCESSED, t('Last accessed'));
        $this->grid->addColumn(self::COL_CREATED, t('Created on'));
        $this->grid->addColumn(self::COL_EXPIRES, t('Expires on'));
    }

    protected function configureActions(): void
    {
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURLs()->list();
    }

    protected function _handleSidebar(): void
    {
        $btn = $this->sidebar->addButton('create_key', t('Create new key').'...')
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->createCollection()->adminURLs()->create())
            ->requireRight(APIScreenRights::SCREEN_API_KEYS_CREATE);

        if($this->createCollection()->countRecords() === 0) {
            $btn->makePrimary();
        }

        parent::_handleSidebar();
    }
}
