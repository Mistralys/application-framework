<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use UI\AdminURLs\AdminURLInterface;

interface RecordStatusScreenInterface extends RecordScreenInterface
{
    public const string URL_NAME = 'status';
    public function getRecordStatusURL() : string|AdminURLInterface;
}
