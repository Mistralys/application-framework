<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens\Submode;

use Application\AppSets\Admin\Screens\Submode\View\SettingsAction;
use Application\AppSets\Admin\Screens\Submode\View\StatusAction;
use Application\AppSets\AppSet;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait;
use Application\AppSets\AppSetsCollection;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI;

/**
 * @property AppSet $record
 */
class ViewSubmode extends BaseRecordSubmode implements SubmodeInterface
{
    use SubmodeTrait;

    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View application set');
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_VIEW;
    }

    public function getDefaultAction(): string
    {
        return StatusAction::URL_NAME;
    }

    public function getDefaultSubscreenClass(): null
    {
        return StatusAction::class;
    }

    public function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    protected function createCollection(): AppSetsCollection
    {
        return AppSetsCollection::getInstance();
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->record->getLabel());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Status'),
            $this->record->adminURL()->status()
        )
            ->setIcon(UI::icon()->status());

        $label = t('Documentation');
        if(!$this->record->hasDocumentation()) {
            $label = (string)sb()->muted($label);
        }

        $this->subnav->addURL(
            $label,
            $this->record->adminURL()->documentation()
        )
            ->setIcon(UI::icon()->help());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->adminURL()->settings()
        )
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->view());
    }
}
