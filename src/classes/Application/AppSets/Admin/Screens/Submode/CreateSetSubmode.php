<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens\Submode;

use Application\AppFactory;
use Application\AppSets\AppSetSettingsManager;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait;
use Application\AppSets\AppSetsCollection;
use DBHelper\Admin\Screens\Submode\BaseRecordCreateSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

final class CreateSetSubmode extends BaseRecordCreateSubmode implements SubmodeInterface
{
    use SubmodeTrait;

    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_APP_SETS_CREATE;
    }

    public function getTitle(): string
    {
        return t('Create a new application set');
    }

    public function getNavigationTitle(): string
    {
        return t('Create new set');
    }

    public function createCollection(): AppSetsCollection
    {
        return AppSetsCollection::getInstance();
    }

    public function getSettingsManager(): AppSetSettingsManager
    {
        return new AppSetSettingsManager($this, null);
    }

    public function getBackOrCancelURL(): string|AdminURLInterface
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The application set %1$s was created successfully at %2$s.',
            sb()->reference($record->getLabel()),
            date('H:i:s')
        );
    }

    protected function _handleAfterSidebar() : void
    {
        AppSetsCollection::getInstance()->injectCoreAreas($this->sidebar);
    }
}
