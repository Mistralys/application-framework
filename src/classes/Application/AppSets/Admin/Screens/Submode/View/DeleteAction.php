<?php

declare(strict_types=1);

namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\AppSets\AppSet;
use Application\AppSets\AppSetsCollection;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\SubmodeInterface;
use Application\Sets\Admin\Traits\SubmodeTrait;
use Application\Sets\Admin\Traits\ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait;
use AppUtils\ClassHelper;
use AppUtils\OperationResult;
use DBHelper\Admin\Screens\Action\BaseRecordDeleteAction;
use DBHelper\Admin\Screens\Submode\BaseRecordDeleteSubmode;
use UI\AdminURLs\AdminURLInterface;

final class DeleteAction extends BaseRecordDeleteAction implements ViewActionInterface
{
    use ViewActionTrait;

    public const string URL_NAME = 'delete';

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
        return t('Delete an application set');
    }

    public function getNavigationTitle(): string
    {
        return t('Delete set');
    }

    protected function _checkPrerequisites(OperationResult $result): void
    {
        $set = $this->getRecord();

        if ($set->isActive()) {
            $result->makeError((string)sb()
                ->t(
                    'Cannot delete the application set %1$s, it is currently active.',
                    sb()->reference($set->getAlias())
                )
                ->t('If you really wish to delete it, activate another one first.')
            );
        }

        if($set->isDefault()) {
            $result->makeError(t('Cannot delete the default application set.'));
        }
    }
}
