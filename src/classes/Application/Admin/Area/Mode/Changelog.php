<?php
/**
 * File containing the {@link Application_Admin_Area_Mode_Changelog} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Area_Mode_Changelog
 */

/**
 * The base admin submode class
 * @see Application_Admin_Area_Mode
 */
require_once 'Application/Admin/Area/Mode.php';

/**
 * The common changelog admin skeleton
 * @see Application_Traits_Admin_RevisionableChangelog
 */
require_once 'Application/Traits/Admin/RevisionableChangelog.php';

/**
 * Base class for changelog screens for revisionable items.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Mode_Changelog extends Application_Admin_Area_Mode
{
    use Application_Traits_Admin_RevisionableChangelog;
    
    public function getDefaultSubmode() : string
    {
        return '';
    }
}