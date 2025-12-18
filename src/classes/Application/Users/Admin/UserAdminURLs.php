<?php

declare(strict_types=1);

namespace Application\Users\Admin;

use Application\AppFactory;
use Application\Users\Admin\Screens\Manage\Mode\ViewMode;
use Application\Users\Admin\Screens\Manage\Mode\View\SettingsSubmode;
use Application\Users\Admin\Screens\Manage\Mode\View\StatusSubmode;
use Application_Users_User;
use UI\AdminURLs\AdminURLInterface;

class UserAdminURLs
{
    private Application_Users_User $user;

    public function __construct(Application_Users_User $user)
    {
        $this->user = $user;
    }

    public function status() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(StatusSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AppFactory::createUsers()
            ->adminURL()
            ->base()
            ->mode(ViewMode::URL_NAME)
            ->int($this->user->getCollection()->getRecordRequestPrimaryName(), $this->user->getID());
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(SettingsSubmode::URL_NAME);
    }
}
