<?php

declare(strict_types=1);

namespace Application\AppSets\Admin;

use Application\AppSets\Admin\Screens\Submode\View\DeleteAction;
use Application\AppSets\Admin\Screens\Submode\View\DocumentationAction;
use Application\AppSets\Admin\Screens\Submode\View\SettingsAction;
use Application\AppSets\Admin\Screens\Submode\View\StatusAction;
use Application\AppSets\AppSet;
use Application\AppSets\AppSetsCollection;
use Application\Development\Admin\Screens\DevelArea;
use Application\Sets\Admin\Screens\AppSetsDevelMode;
use Application\Sets\Admin\Screens\Submode\ViewSubmode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class AppSetAdminURLs
{
    private int $appSetID;

    public function __construct(AppSet $appSet)
    {
        $this->appSetID = $appSet->getID();
    }

    public function delete() : AdminURLInterface
    {
        return $this
            ->view()
            ->action(DeleteAction::URL_NAME);
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->view()
            ->action(StatusAction::URL_NAME);
    }

    public function documentation() : AdminURLInterface
    {
        return $this
            ->view()
            ->action(DocumentationAction::URL_NAME);
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->view()
            ->action(SettingsAction::URL_NAME);
    }

    public function makeActive() : AdminURLInterface
    {
        return $this
            ->status()
            ->bool(StatusAction::REQUEST_PARAM_SET_ACTIVE, true);
    }

    public function view() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(DevelArea::URL_NAME)
            ->mode(AppSetsDevelMode::URL_NAME)
            ->submode(ViewSubmode::URL_NAME)
            ->int(AppSetsCollection::REQUEST_PRIMARY_NAME, $this->appSetID);
    }


}
