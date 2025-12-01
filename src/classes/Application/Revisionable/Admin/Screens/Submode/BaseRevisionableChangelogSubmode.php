<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Submode;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenInterface;
use Application\Revisionable\Admin\RequestTypes\RevisionableScreenTrait;
use Application\Revisionable\Admin\Traits\RevisionableChangelogScreenTrait;

/**
 * Base class for changelog screens for revisionable items.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseRevisionableChangelogSubmode
    extends BaseSubmode
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
    use RevisionableScreenTrait;

    final public function getDefaultAction(): string
    {
        return '';
    }
}
