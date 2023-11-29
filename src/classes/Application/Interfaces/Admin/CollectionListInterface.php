<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application_Admin_ScreenInterface;
use AppUtils\Interfaces\StringableInterface;

interface CollectionListInterface extends Application_Admin_ScreenInterface
{
    public const URL_NAME_DEFAULT = 'list';

    public function getGridName() : string;

    public function getBackOrCancelURL() : string;

    /**
     * @return array<string,string|int|float|StringableInterface|NULL>
     */
    public function getPersistVars() : array;
}
