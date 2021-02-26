<?php
/**
 * File containing the {@link Application_Driver_Interface} interface.
 * 
 * @package Application
 * @subpackage Interfaces
 * @see Application_Driver_Interface
 */

/**
 * Interface for driver classes.
 * 
 * @package Application
 * @subpackage Interfaces
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Driver
 */
interface Application_Driver_Interface
{
    public function start();
    
    public function renderContent();
    
    public function getPageParams(UI_Page $page);
    
    public function getPageID();
    
    public function getRequest();
}