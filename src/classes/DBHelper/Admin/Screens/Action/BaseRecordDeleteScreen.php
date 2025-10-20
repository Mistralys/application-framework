<?php

declare(strict_types=1);

namespace DBHelper\Admin\Screens\Action;

use DBHelper\Admin\Traits\RecordDeleteScreenTrait;

abstract class BaseRecordDeleteScreen extends BaseRecordScreen
{
    use RecordDeleteScreenTrait;
}
