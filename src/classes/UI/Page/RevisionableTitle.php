<?php
/**
 * File containing the {@see UI_Page_RevisionableTitle} class.
 * 
 * @package Application
 * @subpackage User Interface
 * @see UI_Page_RevisionableTitle
 */

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;

/**
 * Wrapper for the page title class to handle revisionable
 * title specifics (state badges, etc.). 
 * 
 * @package Application
 * @subpackage User Interface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Page_Title
 */
class UI_Page_RevisionableTitle extends UI_Renderable
{
    protected Application_Revisionable_Interface $revisionable;
    protected bool $configured = false;
    
    public function __construct(UI_Page $page, Application_Revisionable_Interface $revisionable)
    {
        parent::__construct($page);

        $this->revisionable = $revisionable;
    }

   /**
    * @param string|number|UI_Renderable_Interface $subline
    * @return $this
    */
    public function setSubline($subline) : UI_Page_RevisionableTitle
    {
        $this->renderer->getTitle()->setSubline($subline);
        
        return $this;
    }

   /**
    * @param string|number|UI_Renderable_Interface|NULL $label
    * @return UI_Page_RevisionableTitle
    */
    public function setLabel($label) : UI_Page_RevisionableTitle
    {
        $this->renderer->getTitle()->setText($label);
        
        return $this;
    }
    
   /**
    * @param string|number|UI_Renderable_Interface $subline
    * @return UI_Page_RevisionableTitle
    */
    public function addSubline($subline) : UI_Page_RevisionableTitle
    {
        $this->renderer->getTitle()->addSubline($subline);
        
        return $this;
    }

    /**
     * @param string|number|StringableInterface|NULL $text
     * @return $this
     * @throws UI_Exception
     */
    public function addTextAppend($text) : self
    {
        $this->renderer->getTitle()->addTextAppend($text);
        
        return $this;
    }

   /**
    * @param UI_Interfaces_Badge $badge
    * @return UI_Page_RevisionableTitle
    */
    public function addBadge(UI_Interfaces_Badge $badge) : UI_Page_RevisionableTitle
    {
        $this->renderer->getTitle()->addBadge($badge);
        
        return $this;
    }
    
   /**
    * @param UI_Interfaces_Badge $badge
    * @return UI_Page_RevisionableTitle
    */
    public function prependBadge(UI_Interfaces_Badge $badge) : UI_Page_RevisionableTitle
    {
        $this->renderer->getTitle()->prependBadge($badge);
        
        return $this;
    }
    
    protected function configureBadges(UI_Page_Title $title, Application_Revisionable $revisionable) : void
    {
        $state = $revisionable->getState();

        if($state === null)
        {
            return;
        }

        $badge = $state->getBadge();

        // add the state badge right at the beginning
        $this->prependBadge($badge);

        if($revisionable->isExportable() && $state->getName() === 'draft')
        {
            $this->addBadge(
                UI::label(t('Export notice'))
                ->cursorHelp()
                ->makeInfo()
                ->setIcon(UI::icon()->warning())
                ->setTooltip(t('Drafts are not exported automatically.'))
            );
        }
    }
    
    public function configure() : void
    {
        if($this->configured) {
            return;
        }
        
        $title = $this->renderer->getTitle();
        
        // revisionable that supports states: configfure specific badges
        if($this->revisionable instanceof Application_Revisionable)
        {
            $this->configureBadges($title, $this->revisionable);
        }

        if(Application::getUser()->isDeveloper()) 
        {
            $this->configureDeveloperInfo($title);
        }

        $text = $title->getText();
        
        if(empty($text)) 
        {
            $title->setText($this->revisionable->getLabel());
        }
        
        $this->configured = true;
    }
    
    protected function configureDeveloperInfo(UI_Page_Title $title) : void
    {
        $title->addSubline(sb()
            ->t('Revision:')
            ->sf(
                '<span class="revisionable-title-prettyrev">%s</span>',
                $this->revisionable->getPrettyRevision()
            )
        );
        
        $title->addSubline(sb()
            ->t('Internal revision:')
            ->sf(
                '<span class="revisionable-title-revision">%s</span>',
                $this->revisionable->getRevision()
            )
        );
    }
    
    protected function _render() : string
    {
        return $this->renderer->getTitle()->render();
    }
}
