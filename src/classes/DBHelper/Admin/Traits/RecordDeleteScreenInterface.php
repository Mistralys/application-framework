<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

/**
 * @see RecordDeleteScreenTrait
 */
interface RecordDeleteScreenInterface extends RecordScreenInterface
{
    public const string URL_NAME = 'delete';

    public function getBackOrCancelURL(): string;
}
