<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownAnchor} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownAnchor
 */

/**
 * Bootstrap dropdown anchor element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Bootstrap_DropdownAnchor setName(string $name)
 */
class UI_Bootstrap_DropdownAnchor extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
   /**
    * @var UI_Bootstrap_Anchor
    */
    protected $anchor;

   /**
    * {@inheritDoc}
    * @see UI_Bootstrap::init()
    */
    public function init()
    {
        $this->anchor = $this->ui->createAnchor();
    }
    
    public function setTitle($title)
    {
        return $this->setAttribute('title', $title);
    }
    
    public function setLabel($label)
    {
        $this->anchor->setLabel($label);
        return $this;
    }
    
    public function setHref($url)
    {
        $this->anchor->setHref($url);
        return $this;
    }
    
   /**
    * Sets the target of the anchor tag for the link.
    * 
    * @param string $target
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function setTarget($target)
    {
        $this->anchor->setTarget($target);
        return $this;
    }
    
    public function setOnclick($statement)
    {
        $this->anchor->setClick($statement);
        $this->anchor->setHref('javascript:void(0)');
        return $this;
    }
    
   /**
    * @param UI_Icon $icon
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function setIcon(UI_Icon $icon)
    {
        $this->anchor->setIcon($icon);
        return $this;
    }
    
    public function hasIcon() : bool
    {
        return $this->anchor->hasIcon();
    }
    
    public function getIcon() : ?UI_Icon
    {
        return $this->anchor->getIcon();
    }

    public function makeDangerous()
    {
        return $this->addClass('danger');
    }
    
    public function makeActive()
    {
        return $this->addClass('active');
    }
    
    public function makeDeveloper()
    {
        return $this->addClass('developer');
    }
    
    protected function _render()
    {
        if($this->hasClass('active')) {
            return 
            '<li'.$this->renderAttributes().'>' . 
                '<a>' .
                    $this->anchor->getLabel() .
                '</a>' .  
            '</li>';
        }
        
        if($this->hasClass('developer')) {
            $this->anchor->setLabel(t('DEV:') . ' ' . $this->anchor->getLabel());
        }
        
        return '<li'.$this->renderAttributes().'>' . $this->anchor->render() . '</li>';
    }
}
