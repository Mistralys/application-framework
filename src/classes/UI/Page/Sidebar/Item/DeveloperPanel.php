<?php

use Application\Application;

class UI_Page_Sidebar_Item_DeveloperPanel extends UI_Page_Sidebar_Item
{
    public const int ERROR_SOURCE_BUTTON_NOT_LINKED = 20601;
    
   /**
    * @var UI_Page_Section_Type_Developer
    */
    protected $section;

    protected function initRenderable() : void
    {
        $this->section = $this->page->createDeveloperPanel();
        
        $this->requireFalse(Application::isDemoMode());
        $this->requireTrue(Application::getUser()->isDeveloper(), 'User is not a developer.');
    }
    
   /**
    * Adds a button by converting an existing sidebar button
    * to a developer button. Keeps the original button's 
    * settings (works with linked and form submit buttons).
    * 
    * @param string $buttonName
    * @throws Application_Exception
    * @return UI_Page_Sidebar_Item_DeveloperPanel
    */
    public function addConvertedButton($buttonName)
    {
        $source = $this->sidebar->getButton($buttonName);
        if(!$source || !$source->isValid()) {
            return $this;
        }
        
        $converted = UI::button($source->getLabel());
        
        if($source->isLinked()) 
        {
            $url = $source->getURL();
            if(!strstr($url, '?')) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            
            $url .= 'simulate_only=yes';
            
            $converted->link($url);
        } 
        else if($source->isFormSubmit()) 
        {
            $formName = $source->getFormName();
            
            $converted->click(UI_Form::renderJSSubmitHandler($formName, true));
        } 
        else 
        {
            throw new Application_Exception(
                'Cannot convert button',
                sprintf(
                    'The sidebar button [%s] cannot be converted, only regular linked buttons or form submit buttons can be converted.',
                    $buttonName
                ),
                self::ERROR_SOURCE_BUTTON_NOT_LINKED
            );
        }
        
        if($source->hasIcon()) {
            $converted->setIcon($source->getIcon());
        }
        
        return $this->addButton($converted);
    }

   /**
    * Adds a button to submit a form, formable or datagrid.
    * 
    * @param string|UI_Form|UI_DataGrid|Application_Formable $subject
    * @return UI_Page_Sidebar_Item_DeveloperPanel
    */
    public function addSubmitButton($subject)
    {
        return $this->addButton(
            UI::button(t('Simulate submit'))
            ->click(UI_Form::renderJSSubmitHandler($subject, true))
        );
    }
    
    public function addButton(UI_Button $button)
    {
        $this->section->addButton($button);
        return $this;
    }
    
    public function addSeparator()
    {
        $this->section->addSeparator();
        return $this;
    }
    
    public function addHTML($code)
    {
        $this->section->addHTML($code);
        return $this;
    }
    
    public function addHeading($title)
    {
        $this->section->addHeading($title);
        return $this;
    }
    
    public function appendContent($content)
    {
        $this->section->appendContent($content);
        return $this;
    }
    
    protected function _render() : string
    {
        if(Application::isDemoMode()) {
            return '';
        }
        
        return $this->section->render();
    }
    
   /**
    * @return UI_Page_Section_Type_Developer
    */
    public function getSection() : UI_Page_Section_Type_Developer
    {
        return $this->section;
    }
}
