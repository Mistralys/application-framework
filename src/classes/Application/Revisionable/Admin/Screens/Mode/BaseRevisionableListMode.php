<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Mode;

use Application\Revisionable\Admin\Traits\RevisionableListScreenInterface;
use Application\Revisionable\Admin\Traits\RevisionableListScreenTrait;
use Application_Admin_Area_Mode;

abstract class BaseRevisionableListMode extends Application_Admin_Area_Mode implements RevisionableListScreenInterface
{
    use RevisionableListScreenTrait;

    final public function getDefaultSubmode(): string
    {
        return '';
    }
}
