<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\RequestTypes\APIClientRequestInterface;
use Application\API\Admin\RequestTypes\APIClientRequestTrait;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use AppUtils\ClassHelper;
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
        $key = ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            $record
        );

        return array(
            self::COL_LABEL => $key->getLabel()
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_LABEL, t('Label'));
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
