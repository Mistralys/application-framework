<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ViewArticle;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\Admin\Area\News\BaseViewArticleScreen;
use Application\NewsCentral\NewsScreenRights;
use UI;

/**
 * @property BaseViewArticleScreen $mode
 */
abstract class BaseArticleStatusScreen extends BaseSubmode
{
    public const string URL_NAME = 'status';
    public const string REQUEST_PARAM_PUBLISH = 'publish';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_ARTICLE_STATUS;
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    protected function _handleActions(): bool
    {
        if($this->request->getBool(self::REQUEST_PARAM_PUBLISH)) {
            $this->handlePublishEntry();
        }

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->mode->getNewsEntry()->getAdminStatusURL());
    }

    protected function _handleSidebar(): void
    {
        $entry = $this->mode->getNewsEntry();

        $this->sidebar->addButton('publish-news', t('Publish'))
            ->setIcon(UI::icon()->publish())
            ->makeSuccess()
            ->makeLinked($entry->getAdminPublishURL())
            ->requireFalse($entry->isPublished());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendContent($this->renderPropertyGrid())
            ->makeWithSidebar();
    }

    private function renderPropertyGrid() : string
    {
        $entry = $this->mode->getNewsEntry();

        $grid = $this->ui->createPropertiesGrid();

        $grid->add(t('Language'), $entry->getLocale()->getLabel());
        $grid->addDate(t('Created on'), $entry->getDateCreated())->withTime()->withDiff();
        $grid->addDate(t('Last modified'), $entry->getDateModified())->withTime()->withDiff();
        $grid->add(t('Author'), $entry->getAuthor()->getName());

        $grid->addHeader(sb()->icon(UI::icon()->time())->t('Scheduling'));
        $dateFrom = $entry->getScheduledFromDate();
        $dateTo = $entry->getScheduledToDate();
        if($dateFrom === null && $dateTo === null)
        {
            $grid->addMessage(t('No scheduling is enabled.'))
                ->makeInfo();
        }
        else
        {
            if($dateFrom !== null) {
                $grid->addDate(t('Publish from'), $dateFrom)
                    ->setComment(t('The entry will become visible at this time.'))
                    ->withTime()
                    ->withDiff();
            }

            if($dateTo !== null) {
                $grid->addDate(t('Publish to'), $dateTo)
                    ->setComment(t('The entry will be hidden at this time.'))
                    ->withTime()
                    ->withDiff();
            }
        }

        return $grid->render();
    }

    private function handlePublishEntry() : void
    {
        $entry = $this->mode->getNewsEntry();

        $this->startTransaction();
        $entry->publish();
        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                'The news entry %1$s has been published successfully at %2$s.',
                $entry->getLabel(),
                sb()->time()
            ),
            $entry->getAdminStatusURL()
        );
    }
}
