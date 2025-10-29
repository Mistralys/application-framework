<?php

declare(strict_types=1);

namespace Application\Users\Admin;

use Application\AppFactory;
use Application\Users\Admin\Screens\Mode\BaseViewUserMode;
use Application\Users\Admin\Screens\Submode\BaseUserSettingsSubmode;
use Application\Users\Admin\Screens\Submode\BaseUserStatusSubmode;
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
            ->submode(BaseUserStatusSubmode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AppFactory::createUsers()
            ->adminURL()
            ->base()
            ->mode(BaseViewUserMode::URL_NAME)
            ->int($this->user->getCollection()->getRecordRequestPrimaryName(), $this->user->getID());
    }

    public function settings() : AdminURLInterface
    {
        return $this
            ->base()
            ->submode(BaseUserSettingsSubmode::URL_NAME);
    }
}
