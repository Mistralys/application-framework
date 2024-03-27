<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

use Application\Interfaces\Admin\RevisionableChangelogScreenInterface;
use Application\Traits\Admin\RevisionableChangelogScreenTrait;

/**
 * Base class for changelog screens for revisionable items.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Mode_Changelog
    extends Application_Admin_Area_Mode
    implements RevisionableChangelogScreenInterface
{
    use RevisionableChangelogScreenTrait;
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
}
