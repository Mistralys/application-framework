<?php

declare(strict_types=1);

namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\AppSets\AppSetSettingsManager;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordSettingsAction;
use DBHelper\Interfaces\DBHelperRecordInterface;

final class SettingsAction extends BaseRecordSettingsAction implements ViewActionInterface
{
    use ViewActionTrait;

    public const string URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_EDIT_SET;
    }

    public function getTitle(): string
    {
        return t('Edit application set');
    }

    public function getNavigationTitle(): string
    {
        return t('Edit');
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->user->can(AppSetScreenRights::SCREEN_EDIT_SET);
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSettingsManager(): AppSetSettingsManager
    {
        return new AppSetSettingsManager($this, $this->getRecord());
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The settings have been saved successfully at %1$s.',
            sb()->time()
        );
    }
}
