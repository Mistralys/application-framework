<?php

declare(strict_types=1);

namespace Application\Media\Admin\Screens\Mode;

use Application\AppFactory;
use Application\Media\Admin\MediaScreenRights;
use Application\Media\Admin\Screens\Mode\View\StatusSubmode;
use Application\Media\Admin\Traits\MediaModeInterface;
use Application\Media\Admin\Traits\MediaModeTrait;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property MediaRecord $record
 */
class ViewMode extends BaseRecordMode implements MediaModeInterface
{
    use MediaModeTrait;

    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return MediaScreenRights::SCREEN_VIEW;
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return StatusSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return StatusSubmode::class;
    }

    public function getNavigationTitle(): string
    {
        return t('Media file');
    }

    public function getTitle(): string
    {
        return t('Media file');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->view());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Status'),
            $this->record->adminURL()->status()
        )
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(
            t('Tagging'),
            $this->record->adminURLTagging()
        )
            ->requireTrue(AppFactory::createMediaCollection()->isTaggingEnabled())
            ->setIcon(UI::icon()->tags());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->adminURL()->settings()
        )
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleHelp(): void
    {
        $this->user->getRecent()->getCategoryByAlias(MediaCollection::RECENT_ITEMS_CATEGORY)
            ->addEntry(
                MediaCollection::RECENT_ITEMS_CATEGORY.$this->record->getID(),
                (string)sb()
                    ->add($this->record->getMediaDocument()->getTypeIcon())
                    ->add($this->record->getLabel()),
                (string)$this->record->adminURL()->view()
            );

        $this->renderer
            ->getTitle()
            ->setText($this->record->getLabel())
            ->setIcon($this->record->getMediaDocument()->getTypeIcon());
    }
}
