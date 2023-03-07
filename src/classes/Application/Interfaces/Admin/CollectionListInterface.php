<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application_Admin_ScreenInterface;
use AppUtils\Interface_Stringable;

interface CollectionListInterface extends Application_Admin_ScreenInterface
{
    public const URL_NAME_DEFAULT = 'list';

    public function getGridName() : string;

    public function getBackOrCancelURL() : string;

    /**
     * @return array<string,string|int|float|Interface_Stringable|NULL>
     */
    public function getPersistVars() : array;
}
