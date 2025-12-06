<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Submode;

use Application\Revisionable\Admin\RequestTypes\RevisionableScreenInterface;
use Application\Revisionable\Admin\RequestTypes\RevisionableScreenTrait;
use Application_Admin_Area_Mode_Submode;

abstract class BaseRevisionableRecordSubmode
    extends Application_Admin_Area_Mode_Submode
    implements RevisionableScreenInterface
{
    use RevisionableScreenTrait;
}
