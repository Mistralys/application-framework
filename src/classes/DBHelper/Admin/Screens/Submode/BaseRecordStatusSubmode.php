<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Submode;

use Application\Traits\AllowableMigrationTrait;
use DBHelper\Admin\Traits\RecordStatusScreenInterface;
use DBHelper\Admin\Traits\RecordStatusScreenTrait;

abstract class BaseRecordStatusSubmode extends BaseRecordSubmode implements RecordStatusScreenInterface
{
    use AllowableMigrationTrait;
    use RecordStatusScreenTrait;
}
