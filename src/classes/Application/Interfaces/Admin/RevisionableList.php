<?php

use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * 
 * @see Application_Traits_Admin_RevisionableList
 */
interface Application_Interfaces_Admin_RevisionableList extends AdminScreenInterface
{
   /**
    * @return string
    */
    public function getBackOrCancelURL() : string;

    /**
     * @param string $className
     * @param string $label
     * @param string $redirectURL
     * @param boolean $confirm
     * @return Application_RevisionableCollection_DataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm=false) : Application_RevisionableCollection_DataGridMultiAction;

    public function getCollection() : Application_RevisionableCollection;
}
