<?php

declare(strict_types=1);

namespace Application\Users\Admin;

use Application\Users\Admin\Screens\BaseUsersArea;
use Application\Users\Admin\Screens\Mode\BaseCreateUserMode;
use Application\Users\Admin\Screens\Mode\BaseUserListMode;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class UsersAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(BaseUserListMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(BaseUsersArea::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(BaseCreateUserMode::URL_NAME);
    }
}
