<?php

require_once 'Application/Admin/Area/Mode/Submode.php';

class Application_Admin_Area_Devel_Appsets_Delete extends Application_Admin_Area_Mode_Submode
{
    public function getURLName()
    {
        return 'delete';
    }

    public function getTitle()
    {
        return t('Delete an application set');
    }

    public function getNavigationTitle()
    {
        return t('Delete set');
    }

    public function getDefaultAction()
    {
        return null;
    }

   /**
    * @var Application_Sets
    */
    protected $sets;

   /**
    * @var Application_Sets_Set
    */
    protected $set;
    
    protected function _handleActions()
    {
        $this->sets = $this->driver->getApplicationSets();
        
        $setID = $this->request->getParam('set_id');
        if(empty($setID) || !$this->sets->idExists($setID)) {
            $this->redirectWithErrorMessage(t('Unknown application set.'), $this->sets->getAdminListURL());
        }
        
        $this->set = $this->sets->getByID($setID);
        
        if($this->set->isActive()) {
            $this->redirectWithErrorMessage(
                t('Cannot delete the application set %1$s, it is the one currently used.', $this->set->getID()).' '.
                t('Please choose another set as the current first to be able to delete it.'),
                $this->sets->getAdminListURL()
            );
        }
        
        $this->sets->deleteSet($this->set);
        $this->sets->save();
        
        $this->redirectWithSuccessMessage(
            t(
                'The application set %1$s was deleted successfully at %2$s.', 
                $this->set->getID(),
                date('H:i:s')
            ),
            $this->sets->getAdminListURL()
        );
    }
}