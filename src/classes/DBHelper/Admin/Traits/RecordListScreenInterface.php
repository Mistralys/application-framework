<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface;use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURLInterface;

interface RecordListScreenInterface extends AdminScreenInterface
{
    public const string URL_NAME_DEFAULT = 'list';

    public function getGridName() : string;

    public function getBackOrCancelURL() : string|AdminURLInterface;

    /**
     * @return array<string,string|int|float|StringableInterface|NULL>
     */
    public function getPersistVars() : array;
}
