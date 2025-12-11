<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\AppSetSubmodeInterface;
use Application\Sets\Admin\Traits\AppSetSubmodeTrait;
use Application_Sets;
use Application_Sets_Set;

class DeleteSetSubmode extends BaseSubmode implements AppSetSubmodeInterface
{
    use AppSetSubmodeTrait;

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

    protected Application_Sets $sets;
    protected Application_Sets_Set $set;

    protected function _handleActions(): bool
    {
        $this->sets = AppFactory::createAppSets();

        $setID = $this->request->getParam('set_id');
        if (empty($setID) || !$this->sets->idExists($setID)) {
            $this->redirectWithErrorMessage(t('Unknown application set.'), $this->sets->getAdminListURL());
        }

        $this->set = $this->sets->getByID($setID);

        if ($this->set->isActive()) {
            $this->redirectWithErrorMessage(
                t('Cannot delete the application set %1$s, it is the one currently used.', $this->set->getID()) . ' ' .
                t('Please choose another set as the current first to be able to delete it.'),
                $this->sets->getAdminListURL()
            );
        }

        $this->sets->deleteSet($this->set);
        $this->sets->save();

        $this->redirectWithSuccessMessage(
            t(
                'The application set %1$s was deleted successfully at %2$s.',
                $this->set->getID(),
                date('H:i:s')
            ),
            $this->sets->getAdminListURL()
        );
    }
}
