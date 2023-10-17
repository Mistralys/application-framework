<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News\ViewArticle;

use Application\Admin\Area\Devel\News\BaseViewArticleScreen;
use Application_Admin_Area_Mode_Submode_Action;

/**
 * @property BaseViewArticleScreen $submode
 */
class BaseArticleStatusScreen extends Application_Admin_Area_Mode_Submode_Action
{
    public const URL_NAME = 'status';

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

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->submode->getNewsEntry()->getAdminStatusURL());
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->makeWithSidebar();
    }
}
