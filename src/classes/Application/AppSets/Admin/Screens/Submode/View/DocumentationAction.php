<?php

declare(strict_types=1);

namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordAction;
use UI_Themes_Theme_ContentRenderer;

class DocumentationAction extends BaseRecordAction implements ViewActionInterface
{
    use ViewActionTrait;

    public const string URL_NAME = 'documentation';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_DELETE_SET;
    }

    public function getTitle(): string
    {
        return t('Application set documentation');
    }

    public function getNavigationTitle(): string
    {
        return t('Documentation');
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->getRecord()->renderDocumentation())
            ->makeWithoutSidebar();
    }
}
