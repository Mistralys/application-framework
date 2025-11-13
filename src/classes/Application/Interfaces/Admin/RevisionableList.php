<?php

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Revisionable\Collection\BaseRevisionableDataGridMultiAction;
use Application\Revisionable\Collection\BaseRevisionableCollection;
use Application\Revisionable\Collection\RevisionableCollectionInterface;

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
     * @return BaseRevisionableDataGridMultiAction
     */
    public function addMultiAction(string $className, string $label, string $redirectURL, bool $confirm=false) : BaseRevisionableDataGridMultiAction;

    public function getCollection() : RevisionableCollectionInterface;
}
