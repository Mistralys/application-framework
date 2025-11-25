<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Submode;

use Application\Revisionable\Admin\Traits\RevisionableListScreenInterface;
use Application\Revisionable\Admin\Traits\RevisionableListScreenTrait;
use Application_Admin_Area_Mode_Submode;

abstract class BaseRevisionableListSubmode
    extends Application_Admin_Area_Mode_Submode
    implements RevisionableListScreenInterface
{
    use RevisionableListScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}

