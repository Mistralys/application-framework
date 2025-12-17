<?php

declare(strict_types=1);

namespace Application\API\Admin\Traits;

use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeySettingsAction;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeyStatusAction;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Clients\Keys\APIKeysCollection;
use AppUtils\ClassHelper;
use UI;
use UI\AdminURLs\AdminURLInterface;

trait APIKeyActionRecordTrait
{
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

        $this->tabs->appendTab(t('Status'), APIKeyStatusAction::URL_NAME)
            ->setIcon(UI::icon()->status())
            ->makeLinked($apiKey->adminURL()->status());

        $this->tabs->appendTab(t('Key Settings'), APIKeySettingsAction::URL_NAME)
            ->setIcon(UI::icon()->settings())
            ->makeLinked($apiKey->adminURL()->settings());

        $this->tabs->selectByAction();
    }
}
