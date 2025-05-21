<?php

declare(strict_types=1);

class UI_Form_Renderer_Registry implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private UI_Form_Renderer $renderer;
    private string $id;
    private bool $enabled = false;
    private bool $injected = false;
    private UI $ui;
    private string $logIdentifier;

    public function __construct(UI_Form_Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->ui = $renderer->getUI();
        $this->id = 'freg'.nextJSID();
        $this->logIdentifier = sprintf('UI [%s] | FormRenderer | Registry | %s', $this->ui->getInstanceKey(), $this->id);
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function setEnabled(bool $enabled) : void
    {
        $this->enabled = $enabled;
    }
    
    public function injectJS() : void
    {
        if($this->injected)
        {
            return;
        }

        $this->log('InjectJS | Injecting the registry.');

        $this->ui->addJavascriptHead(sprintf(
            "var %s = FormHelper.getRegistry('%s')",
            $this->id,
            $this->renderer->getForm()->getName()
        ));
        
        $this->injectSections();
        $this->injectElements($this->renderer->getRootElements());
        
        $this->injected = true;
    }
    
    private function injectSections() : void
    {
        $sections = $this->renderer->getSections()->getAll();

        $this->log('InjectJS | Found [%s] sections.', count($sections));

        foreach($sections as $section)
        {
            $this->ui->addJavascriptHeadStatement(
                sprintf('%s.AddSection', $this->id),
                $section->getID(),
                $section->getLabel()
            );
        }
    }
    
    private function injectElements(UI_Form_Renderer_ElementFilter $filter) : void
    {
        $elements = $filter->getFiltered();

        $this->log('InjectJS | Found [%s] elements.', count($elements));

        foreach($elements as $element)
        {
            if($element->includeInRegistry())
            {
                $this->ui->addJavascriptHeadStatement(
                    sprintf('%s.AddElement', $this->id),
                    $element->getElementID(),
                    $element->getElementLabel(),
                    $element->getElementTypeID(),
                    $element->getSectionID()
                );
            }
            
            $this->injectElements($element->getTypeRenderer()->getSubElements());
        }
    }
}
