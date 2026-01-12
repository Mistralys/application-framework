<?php

declare(strict_types=1);

namespace Application\API\Admin\Screens\Mode;

use Application\API\Admin\APIScreenRights;
use Application\API\Admin\Screens\Mode\View\ClientStatusSubmode;
use Application\API\Admin\Traits\ClientModeInterface;
use Application\API\Admin\Traits\ClientModeTrait;
use Application\API\Clients\APIClientRecord;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class ViewClientMode extends BaseRecordMode implements ClientModeInterface
{
    use ClientModeTrait;

    public const string URL_NAME = 'view';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('View API Client');
    }

    public function getNavigationTitle() : string
    {
        return t('View Client');
    }

    protected function _handleBreadcrumb(): void
    {
        $record = $this->getRecord();

        $this->breadcrumb->appendItem($record->getLabel())
            ->makeLinked($this->getBreadcrumbLink());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->getRecord()->getLabel());
    }

    public function getRecord(): APIClientRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            APIClientRecord::class,
            parent::getRecord()
        );
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(t('Status'), $this->getRecord()->adminURL()->status())
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(t('API Keys'), $this->getRecord()->adminURL()->apiKeys())
            ->setIcon(UI::icon()->apiKeys());

        $this->subnav->addURL(t('Client Settings'), $this->getRecord()->adminURL()->settings())
            ->setIcon(UI::icon()->settings());
    }

    private function getBreadcrumbLink() : AdminURLInterface
    {
        return AdminURL::create()
            ->area($this->getArea()->getURLName())
            ->mode($this->getURLName())
            ->int($this->createCollection()->getRecordRequestPrimaryName(), $this->getRecord()->getID());
    }

    public function getRequiredRight(): string
    {
        return APIScreenRights::SCREEN_CLIENTS_VIEW;
    }

    public function getDefaultSubmode(): string
    {
        return ClientStatusSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ClientStatusSubmode::class;
    }
}
