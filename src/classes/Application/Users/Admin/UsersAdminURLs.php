<?php

declare(strict_types=1);

namespace Application\Users\Admin;

use Application\Users\Admin\Screens\Manage\Mode\CreateMode;
use Application\Users\Admin\Screens\Manage\Mode\ListMode;
use Application\Users\Admin\Screens\Manage\ManageUsersArea;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLInterface;

class UsersAdminURLs
{
    public function list() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(ListMode::URL_NAME);
    }

    public function base() : AdminURLInterface
    {
        return AdminURL::create()
            ->area(ManageUsersArea::URL_NAME);
    }

    public function create() : AdminURLInterface
    {
        return $this
            ->base()
            ->mode(CreateMode::URL_NAME);
    }
}
