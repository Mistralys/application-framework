<?php

declare(strict_types=1);

namespace Application\Admin\Area\News\ViewArticle;

use Application\Admin\Area\News\BaseViewArticleScreen;
use Application_Admin_Area_Mode_Submode;
use UI;

/**
 * @property BaseViewArticleScreen $mode
 */
abstract class BaseArticleStatusScreen extends Application_Admin_Area_Mode_Submode
{
    public const URL_NAME = 'status';
    public const REQUEST_PARAM_PUBLISH = 'publish';

    public function getURLName(): string
    {
        return self::URL_NAME;
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
