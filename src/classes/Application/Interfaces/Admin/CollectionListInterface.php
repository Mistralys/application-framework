<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application_Admin_ScreenInterface;

interface CollectionListInterface extends Application_Admin_ScreenInterface
{
    public const URL_NAME_DEFAULT = 'list';

    public function getBackOrCancelURL() : string;
}
