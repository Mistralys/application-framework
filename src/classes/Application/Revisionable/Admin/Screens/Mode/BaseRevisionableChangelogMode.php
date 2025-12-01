<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Revisionable\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
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
abstract class BaseRevisionableChangelogMode
    extends BaseMode
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
    use RevisionableScreenTrait;

    final public function getDefaultSubmode(): string
    {
        return '';
    }
}
