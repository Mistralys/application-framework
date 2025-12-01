<?php
/**
 * @package API
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\API\Admin\Screens;

use Application\API\Admin\APIScreenRights;
use Application\API\Clients\APIClientRecord;
use Application\API\Clients\APIClientRecordSettings;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;
use DBHelper\Admin\Screens\Mode\BaseRecordCreateMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * Abstract base class for the API Client creation screen.
 *
 * @package API
 * @subpackage Admin
 */
abstract class BaseCreateAPIClientMode extends BaseRecordCreateMode
{
    public const string URL_NAME = 'create';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Create a new API Client');
    }

    public function getNavigationTitle(): string
    {
        return t('Create new client');
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_CREATE;
    }

    public function createCollection() : APIClientsCollection
    {
        return AppFactory::createAPIClients();
    }

    public function getSettingsManager() : APIClientRecordSettings
    {
        return new APIClientRecordSettings($this);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The API Client %1$s has been created successfully at %2$s.',
            sb()->reference($record->getLabel()),
            sb()->time()
        );
    }

    public function getSuccessURL(DBHelperRecordInterface $record): AdminURLInterface
    {
        if($record instanceof APIClientRecord) {
            return $record->adminURL()->settings();
        }

        return $this->createCollection()->adminURL()->list();
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function getAbstract(): string
    {
        return t('Once the client has been created, you will be able to generate API keys for it.');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->createCollection()->adminURL()->create());
    }
}
