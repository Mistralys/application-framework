<?php
/**
 * File containing the {@see Application_Admin_Area_Devel_Appinterface} class.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Area_Devel_Appinterface
 */

declare(strict_types=1);

/**
 * Abstract base class used to display a reference of application UI
 * elements that can be used when building administration screens.
 * It creates a live menu to choose which examples to show.
 * 
 * The examples themselves are stored as templates, under
 * `templates/appinterface`, with folders corresponding to the
 * example's category ID.
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Admin_Area_Devel_Appinterface extends Application_Admin_Area_Mode
{
   /**
    * @var array<string,array<string,mixed>>
    */
    private $examples;
    
   /**
    * @var string[]
    */
    private $exampleIDs = array();
    
   /**
    * @var string
    */
    private $activeExample;
    
    public function getURLName()
    {
        return 'appinterface';
    }

    public function getTitle()
    {
        return t('Application interface reference');
    }

    public function getNavigationTitle()
    {
        return t('Interface refs');
    }

    public function getDefaultSubmode()
    {
        return null;
    }

    public function isUserAllowed()
    {
        return $this->user->isDeveloper();
    }
    
    protected function getExamplesList() : array
    {
        return array(
            'content-sections' => array(
                'label' => t('Content sections'),
                'examples' => array(
                    'section' => t('Basic usage'),
                    'section-adding-html' => t('Adding HTML contents'),
                    'section-info' => t('Informational message section'),
                    'section-compact' => t('Compact mode'),
                    'section-itemslist' => t('Adding an items list'),
                    'section-form' => t('Adding a form'),
                    'section-quickselector' => t('Adding a quick selector'),
                    'section-subsection' => t('Adding a subsection'),
                    'section-buttons' => t('Adding context buttons'),
                )
            ),
            'forms' => array(
                'label' => t('Forms'),
                'examples' => array(
                    'sections' => t('Automatic sections'),
                    'tabs' => t('With tabs'),
                    'custom-elements' => t('Custom app input elements')
                )
            ),
            'errors' => array(
                'label' => t('Error handling'),
                'examples' => array(
                    'exception-html' => t('The exception page, in %1$s format', 'HTML'),
                    'exception-text' => t('The exception page, in %1$s format', 'TEXT'),
                )
            )
        );
    }
    
    protected function _handleActions()
    {
        $this->examples = $this->getExamplesList();
        
        $categoryIDs = array_keys($this->examples);
        foreach($categoryIDs as $categoryID) {
            $exampleIDs = array_keys($this->examples[$categoryID]['examples']);
            foreach($exampleIDs as $exampleID) {
                $this->exampleIDs[] = $categoryID.'.'.$exampleID;
            }
        }
        
        $active = $this->request->getParam('example');
        if(!empty($active) && in_array($active, $this->exampleIDs)) {
            $this->activeExample = $active;
        }
    }

    protected function _handleSidebar()
    {
        foreach($this->examples as $categoryID => $category) {
            $section = $this->sidebar->addSection()
            ->setTitle($category['label'])
            ->makeCollapsible();
            
            $section->appendContent('<ul class="unstyled">');
            foreach($category['examples'] as $exampleID => $exampleTitle) {
                $section->appendContent(
                    '<li>'.
                        '<a href="'.$this->getURL(array('example' => $categoryID.'.'.$exampleID)).'">'.
                            $exampleTitle.
                        '</a>'.
                    '</li>'
                );
            }
            $section->appendContent('</ul>');
        }
    }
    
    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())->makeLinkedFromMode($this);
    }
    
    protected function _renderContent()
    {
        return $this->renderContentWithSidebar(
            $this->renderTemplate(
                'devel.appinterface',
                array(
                    'examples' => $this->examples,
                    'active' => $this->activeExample
                )
            ),
            $this->getTitle()
        );
    }
}
