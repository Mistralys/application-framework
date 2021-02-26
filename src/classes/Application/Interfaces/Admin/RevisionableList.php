<?php

/**
 * 
 * @see Application_Traits_Admin_RevisionableList
 */
interface Application_Interfaces_Admin_RevisionableList extends Application_Admin_ScreenInterface
{
   /**
    * @return string
    */
    public function getBackOrCancelURL();

    /**
     * @param string $className
     * @param string $label
     * @param string $redirectURL
     * @param boolean $confirm
     * @return Application_RevisionableCollection_DataGridMultiAction
     */
    public function addMultiAction($className, $label, $redirectURL, $confirm=false);
}
