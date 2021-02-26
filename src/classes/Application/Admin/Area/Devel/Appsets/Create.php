<?php

require_once 'Application/Admin/Area/Mode/Submode.php';

class Application_Admin_Area_Devel_Appsets_Create extends Application_Admin_Area_Mode_Submode
{
    public function getURLName()
    {
        return 'create';
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
    
    protected function _handleActions()
    {
        $this->sets = $this->driver->getApplicationSets();
        $this->areas = $this->driver->getAdminAreaObjects();
        
        $this->createSettingsForm();
        
        if(!$this->isFormValid()) {
            return;
        }
        
        $set = Application_Sets_Set::createFromFormable($this);
        $this->sets->save();
        
        $this->redirectWithSuccessMessage(
            t(
                'The application set %1$s was created successfully at %2$s.', 
                $set->getID(),
                date('H:i:s')
            ),
            $this->sets->getAdminListURL()
        );
    }

    protected function _handleSidebar()
    {
        $this->sidebar->addButton('addset', t('Add new set'))
        ->setIcon(UI::icon()->add())
        ->makePrimary()
        ->makeClickable(sprintf("application.submitForm('%s')", $this->formableForm->getName()));
        
        $this->sidebar->addButton('cancel', t('Cancel'))
        ->makeLinked($this->sets->getAdminListURL());
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
        Application_Sets_Set::createSettingsForm($this);
        
        $this->addFormablePageVars();
    }
}