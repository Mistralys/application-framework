<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Submode;

use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenInterface;
use Application\Revisionable\Admin\RequestTypes\RevisionableScreenTrait;
use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenTrait;
use Application_Admin_Area_Mode_Submode;

/**
 * Base class for changelog screens for revisionable items.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseRevisionableChangelogSubmode
    extends Application_Admin_Area_Mode_Submode
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
    use RevisionableScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
