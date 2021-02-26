<?php
/**
 * This template is used to render the upper part of every
 * page, from the breadcrumb to the page abstract. After this,
 * the content with or without sidebar is shown.
 * 
 * @package Application
 * @subpackage Themes
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see frame.content.with-sidebar
 * @see frame.content.without-sidebar
 */

    /* @var $this UI_Page_Template */
    
    $area = $this->driver->getActiveArea();
    $mode = $area->getMode();
    
    $this->page->getBreadcrumb()->display();
    
    // if the page has a quick selector set, prepend that to
    // any existing content that may be set for the right of
    // the page title.
    if($this->page->hasQuickSelector('title-right'))
    {
        $this->renderer->getTitle()->addContextElement($this->page->getQuickSelector('title-right'));
    }
    
    // The main page title
    if($this->renderer->hasTitle())
    {
        $title = $this->renderer->getTitle();
        
        // the browser titlebar title
        $pageTitle = $this->page->getTitle();
        if(empty($pageTitle)) 
        {
            $this->page->setTitle(sb()
                ->add(strip_tags($title->getText()))
                ->add('-')
                ->add($this->driver->getAppNameShort())
            );
        }
        
        $title->display();
    }
    
    // add the sub navigation if present
    if($this->page->hasSubnavigation())
    {
        $this->page->getSubnavigation()->display();
    }
    
    // the page subtitle if present
    if($this->renderer->hasSubtitle())
    {
        $this->renderer->getSubtitle()->display();
    }
    
    // add the content tabs if present
    if($area->hasTabs())
    {
        $area->getTabs()->display();
    }
    
    if($this->renderer->hasAbstract())
    {
        echo sprintf(
            '<h3 class="page-abstract">%s</h3>',
            $this->renderer->getAbstract()
        );
    }
