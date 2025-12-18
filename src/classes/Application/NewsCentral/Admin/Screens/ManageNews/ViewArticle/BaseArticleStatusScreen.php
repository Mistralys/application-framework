<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode\ViewArticle;

use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Traits\ViewArticleSubmodeInterface;
use Application\NewsCentral\Admin\Traits\ViewArticleSubmodeTrait;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use NewsCentral\Entries\NewsEntry;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

/**
 * @property NewsEntry $record
 */
class BaseArticleStatusScreen extends BaseRecordStatusSubmode implements ViewArticleSubmodeInterface
{
    use ViewArticleSubmodeTrait;

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

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->record->adminURL()->status();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('publish-news', t('Publish'))
            ->setIcon(UI::icon()->publish())
            ->makeSuccess()
            ->makeLinked($this->record->adminURL()->publish())
            ->requireFalse($this->record->isPublished());
    }

    private function handlePublishEntry() : void
    {
        $this->startTransaction();
        $this->record->publish();
        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                'The news entry %1$s has been published successfully at %2$s.',
                $this->record->getLabel(),
                sb()->time()
            ),
            $this->record->adminURL()->status()
        );
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $entry = ClassHelper::requireObjectInstanceOf(
            NewsEntry::class,
            $record
        );

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
    }
}
