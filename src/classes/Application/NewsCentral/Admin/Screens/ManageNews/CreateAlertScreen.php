<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\NewsSettingsManager;
use DBHelper\Interfaces\DBHelperRecordInterface;

class CreateAlertScreen extends CreateArticleScreen
{
    public const string URL_NAME = 'create-alert';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_CREATE_ALERT;
    }

    public function getSettingsManager() : NewsSettingsManager
    {
        return parent::getSettingsManager()->makeAlert();
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The news alert has been created successfully at %1$s.',
            sb()->time()
        );
    }

    public function getTitle(): string
    {
        return t('Create a news alert');
    }

    public function getAbstract(): string
    {
        return (string)sb()
            ->t('This lets you add a news alert, which will be shown prominently in the user interface.')
            ->note()
            ->t('It will not be published right away after saving, it will be added as a draft.');
    }
}
