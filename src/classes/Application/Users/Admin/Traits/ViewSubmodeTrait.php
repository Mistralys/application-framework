<?php

declare(strict_types=1);

namespace Application\Users\Admin\Traits;

use Application\AppFactory;
use Application\Users\Admin\Screens\Manage\Mode\ViewMode;
use Application_Users;
use UI\AdminURLs\AdminURLInterface;

trait ViewSubmodeTrait
{
    public function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    public function getParentScreenClass() : string
    {
        return ViewMode::class;
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getBackOrCancelURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }
}
