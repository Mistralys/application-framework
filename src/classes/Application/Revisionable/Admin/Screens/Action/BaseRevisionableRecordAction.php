<?php

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Action;

use Application\Revisionable\Admin\RequestTypes\RevisionableScreenInterface;
use Application\Revisionable\Admin\RequestTypes\RevisionableScreenTrait;
use Application\Revisionable\RevisionableInterface;
use Application_Admin_Area_Mode_Submode_Action;

/**
 * @property RevisionableInterface $revisionable
 */
abstract class BaseRevisionableRecordAction
    extends Application_Admin_Area_Mode_Submode_Action
    implements RevisionableScreenInterface
{
    use RevisionableScreenTrait;
}
