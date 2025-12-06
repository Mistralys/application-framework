<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Action;

use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseRevisionableStatusAction extends BaseRevisionableRecordAction
{
    public const string URL_NAME = 'status';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    protected function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        $revisionable = $this->getRevisionableOrRedirect();

        $table = $this->ui->createPropertiesGrid($this->createCollection()->getRecordTypeName() . '_status')->makeSection();

        $this->injectProperties($table);

        $table->injectRevisionDetails($revisionable, $revisionable->getAdminChangelogURL());

        return $this->renderer
            ->appendContent($table)
            ->makeWithSidebar();
    }

    abstract protected function injectProperties(UI_PropertiesGrid $grid);
}