<?php

declare(strict_types=1);

namespace Application\Users;

use Application\AppFactory;
use Application_Formable_RecordSelector;
use Application_Formable_RecordSelector_Entry;
use Application_Users;

class UserSelector extends Application_Formable_RecordSelector
{
    public function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    protected function configureFilters(): void
    {
    }

    protected function configureEntry(Application_Formable_RecordSelector_Entry $entry): void
    {
    }
}
