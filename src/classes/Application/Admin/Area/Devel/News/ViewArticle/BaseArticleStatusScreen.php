<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News\ViewArticle;

use Application\Admin\Area\Devel\News\BaseViewArticleScreen;
use Application_Admin_Area_Mode_Submode_Action;
use UI;

/**
 * @property BaseViewArticleScreen $submode
 */
class BaseArticleStatusScreen extends Application_Admin_Area_Mode_Submode_Action
{
    public const URL_NAME = 'status';
    public const REQUEST_PARAM_PUBLISH = 'publish';

    public function getURLName(): string
    {
        return self::URL_NAME;
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
            ->makeLinked($this->submode->getNewsEntry()->getAdminStatusURL());
    }

    protected function _handleSidebar(): void
    {
        $entry = $this->submode->getNewsEntry();

        $this->sidebar->addButton('publish-news', t('Publish'))
            ->setIcon(UI::icon()->publish())
            ->makeSuccess()
            ->makeLinked($entry->getAdminPublishURL())
            ->requireFalse($entry->isPublished());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendContent($this->renderPropertiesGrid())
            ->makeWithSidebar();
    }

    private function renderPropertiesGrid() : string
    {
        $entry = $this->submode->getNewsEntry();

        $grid = $this->ui->createPropertiesGrid();

        $grid->addDate(t('Created on'), $entry->getDateCreated())->withTime()->withDiff();
        $grid->addDate(t('Last modified'), $entry->getDateModified())->withTime()->withDiff();
        $grid->add(t('Author'), $entry->getAuthor()->getName());

        return $grid->render();
    }

    private function handlePublishEntry() : void
    {
        $entry = $this->submode->getNewsEntry();

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
