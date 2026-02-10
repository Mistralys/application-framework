<?php

declare(strict_types=1);

namespace Application\AppSets;

use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Welcome\Screens\WelcomeArea;
use AppUtils\ArrayDataCollection;

final class DefaultAppSet extends AppSet
{
    public function __construct()
    {
        parent::__construct(
            AppSetsCollection::DEFAULT_ID,
            AppSetsCollection::getInstance()
        );

        ArrayDataCollection::create();
    }

    protected function loadData(): array
    {
        return array(
            AppSetsCollection::PRIMARY_NAME => AppSetsCollection::DEFAULT_ID,
            AppSetsCollection::COL_ALIAS => AppSetsCollection::DEFAULT_ALIAS,
            AppSetsCollection::COL_LABEL => t('Default Set'),
            AppSetsCollection::COL_DEFAULT_URL_NAME => WelcomeArea::URL_NAME,
            AppSetsCollection::COL_URL_NAMES => implode(',', AdminScreenIndex::getInstance()->getAdminAreaURLNames())
        );
    }

    // Ignore attempts to set record keys.
    public function setRecordKey(string $name, mixed $value): bool
    {
        return false;
    }

    // Avoid running a database query to check for record key existence.
    public function recordKeyExists(string $name): bool
    {
        return false;
    }

    // There is nothing to save for the default set.
    public function save(bool $silent = false): bool
    {
        return true;
    }
}
