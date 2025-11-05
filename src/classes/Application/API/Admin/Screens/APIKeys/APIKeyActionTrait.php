<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\APIKeys;

use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use AppUtils\ClassHelper;
use UI;
use UI\AdminURLs\AdminURLInterface;

trait APIKeyActionTrait
{
    public function createCollection(): APIKeysCollection
    {
        return $this->getAPIClientRequest()->getRecordOrRedirect()->createAPIKeys();
    }

    public function getCollection() : APIKeysCollection
    {
        return ClassHelper::requireObjectInstanceOf(
            APIKeysCollection::class,
            parent::getCollection()
        );
    }

    public function getRecord() : APIKeyRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            APIKeyRecord::class,
            parent::getRecord()
        );
    }

    public function getRecordMissingURL(): string|AdminURLInterface
    {
        return $this->getCollection()->adminURLs()->list();
    }

    public function getRecordStatusURL(): string|AdminURLInterface
    {
        return $this->getRecord()->adminURL()->status();
    }

    protected function _handleHelp() : void
    {
        $this->renderer
            ->getSubtitle()
            ->setText($this->getRecord()->getLabel())
            ->setIcon(UI::icon()->apiKeys());
    }

    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendItem($this->getRecord()->getLabel())
            ->makeLinked($this->getRecord()->adminURL()->base());

        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getCurrentScreenURL());
    }

    abstract protected function getCurrentScreenURL() : AdminURLInterface;

    protected function _handleTabs() : void
    {
        $apiKey = $this->getRecord();

        $this->tabs->appendTab(t('Status'), BaseAPIKeyStatusAction::URL_NAME)
            ->setIcon(UI::icon()->status())
            ->makeLinked($apiKey->adminURL()->status());

        $this->tabs->appendTab(t('Key Settings'), BaseAPIKeySettingsAction::URL_NAME)
            ->setIcon(UI::icon()->settings())
            ->makeLinked($apiKey->adminURL()->settings());

        $this->tabs->selectByAction();
    }
}
