<?php
/**
 * File containing the {@link Application_Admin_Area_Mode_Submode_Changelog} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Area_Mode_Submode_Changelog
 */

/**
 * Base class for changelog screens for revisionable items.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Mode_Submode_Changelog extends Application_Admin_Area_Mode_Submode
{
    use Application_Traits_Admin_RevisionableChangelog;
    
    public function getDefaultAction() : string
    {
        return '';
    }
}