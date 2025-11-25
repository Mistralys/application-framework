<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Traits\RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait;

abstract class BaseRecordStatusAction extends BaseRecordAction implements RecordStatusScreenInterface
{
    use AllowableMigrationTrait;
    use RecordStatusScreenTrait;
}
