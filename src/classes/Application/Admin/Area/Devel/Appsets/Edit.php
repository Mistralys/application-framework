<?php

require_once 'Application/Admin/Area/Mode/Submode.php';

class Application_Admin_Area_Devel_Appsets_Edit extends Application_Admin_Area_Mode_Submode
{
    public function getURLName()
    {
        return 'edit';
    }

    public function getTitle()
    {
        return t('Create a new application set');
    }

    public function getNavigationTitle()
    {
        return t('Create new set');
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
    * @var Application_Admin_Area[]
    */
    protected $areas;
    
   /**
    * @var Application_Sets_Set
    */
    protected $set;
    
    protected function _handleActions()
    {
        $this->sets = $this->driver->getApplicationSets();
        $this->areas = $this->driver->getAdminAreaObjects();
        
        $setID = $this->request->getParam('set_id');
        if(empty($setID) || !$this->sets->idExists($setID)) {
            $this->redirectWithErrorMessage(t('Unknown application set.'), $this->sets->getAdminListURL());
        }
        
        $this->set = $this->sets->getByID($setID);
        
        $this->createSettingsForm();
        
        if(!$this->isFormValid()) {
            return;
        }

        $this->set->updateFromForm($this->getFormValues());
        $this->sets->save();
        
        $this->redirectWithSuccessMessage(
            t(
                'The application set %1$s was updated successfully at %2$s.', 
                $this->set->getID(),
                date('H:i:s')
            ),
            $this->sets->getAdminListURL()
        );
    }

    protected function _handleSidebar()
    {
        $this->sidebar->addButton('saveset', t('Save'))
        ->setIcon(UI::icon()->add())
        ->makePrimary()
        ->makeClickable(sprintf("application.submitForm('%s')", $this->formableForm->getName()));
        
        $this->sidebar->addButton('cancel', t('Cancel'))
        ->makeLinked($this->sets->getAdminListURL());
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addButton('deleteset', t('Delete...'))
        ->setIcon(UI::icon()->delete())
        ->makeLinked($this->set->getAdminDeleteURL())
        ->makeDangerous()
        ->makeConfirm(
            '<p>'.
                '<b>'.t('This will delete the application set %1$s.', '<code>'.$this->set->getID().'</code>').'</b>'.
            '</p>'.
            '<p>'.
                t('Note:').' '.t('An exception will be thrown if this set is still used in the code afterwards.').
            '</p>'.
            '<p>'.
                '<b class="text-warning">'.t('This cannot be undone, are you sure?').'</b>'.
            '</p>'
        );
    }
    
    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromSubmode($this);
    }
    
    protected function _renderContent()
    {
        return $this->renderForm(
            $this->getTitle(), 
            $this->formableForm
        );
    }
    
    protected $formName = 'appsets';
    
    protected function createSettingsForm()
    {
        Application_Sets_Set::createSettingsForm($this, $this->set);
        
        $this->addFormablePageVars();
    }
}