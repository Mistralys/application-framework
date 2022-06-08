<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownAnchor} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownAnchor
 */

use AppUtils\OutputBuffering;

/**
 * Bootstrap dropdown anchor element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownAnchor
    extends UI_Bootstrap
    implements
        Application_Interfaces_Iconizable,
        UI_Interfaces_Bootstrap_DropdownItem
{
   /**
    * @var UI_Bootstrap_Anchor
    */
    protected UI_Bootstrap_Anchor $anchor;

    public function init() : void
    {
        $this->anchor = $this->ui->createAnchor();

        $this->addClass('dropdown-item');
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return UI_Bootstrap_DropdownAnchor
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        return $this->setAttribute('title', toString($title));
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : self
    {
        $this->anchor->setLabel($label);
        return $this;
    }
    
    public function setHref(string $url) : self
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
    public function setTarget(string $target) : self
    {
        $this->anchor->setTarget($target);
        return $this;
    }
    
    public function setOnclick(string $statement) : self
    {
        $this->anchor->setClick($statement);
        $this->anchor->setHref('javascript:void(0)');
        return $this;
    }
    
   /**
    * @param UI_Icon|NULL $icon
    * @return UI_Bootstrap_DropdownAnchor
    */
    public function setIcon(?UI_Icon $icon) : self
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

    public function makeDangerous() : self
    {
        return $this->addClass('danger');
    }
    
    public function makeActive() : self
    {
        return $this->addClass('active');
    }
    
    public function makeDeveloper() : self
    {
        return $this->addClass('developer');
    }
    
    protected function _render() : string
    {
        OutputBuffering::start();

        ?>
        <li <?php echo $this->renderAttributes() ?>>
            <?php echo $this->anchor->render() ?>
        </li>
        <?php
        
        return OutputBuffering::get();
    }
}
