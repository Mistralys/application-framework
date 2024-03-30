<?php

declare(strict_types=1);

use Application\Interfaces\Admin\RevisionableChangelogScreenInterface;
use Application\Traits\Admin\RevisionableChangelogScreenTrait;

abstract class Application_Admin_Area_Mode_Submode_Action_RevisionableChangelog
    extends Application_Admin_Area_Mode_Submode_Action_Revisionable
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
}
