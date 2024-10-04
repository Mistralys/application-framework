<?php
/**
 * File containing the {@see Application_Interfaces_Admin_ScreenDisplayMode} interface.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Interfaces_Admin_ScreenDisplayMode
 */

declare(strict_types=1);

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * Matching interface for the ScreenDisplayMode trait.
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_ScreenDisplayMode
 */
interface Application_Interfaces_Admin_ScreenDisplayMode extends AdminScreenInterface
{
    public function resolveDisplayMode() : string;
    
    public function getDefaultDisplayMode() : string;
}
