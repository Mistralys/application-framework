<?php
/**
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_Title
 */

    /* @var $this UI_Page_Template */
    /* @var $title UI_Page_Title */
    
    $title = $this->getVar('title');
    
    $text = $title->getText();

    $string = sb();

    if($title->hasIcon())
    {
        $string->icon($title->getIcon());
    }
    
    // force displaying the title even if empty, to allow
    // empty titles but with context elements. 
    if(empty($text)) 
    {
        $text = '&#160;';
    }
    
    $string->add($text);
    
    if($title->hasAppends())
    {
        $string->add(implode(' ', $title->getAppends()));
    }
    
    if($title->hasBadges())
    {
        $badges = array_map('toString', $title->getBadges());
        
        $string->html(sprintf(
            '<span class="title-badges">%s</span>',
            implode(' ', $badges)
        ));
    }

    if($title->hasSubline())
    {
        $string->html(sprintf(
            '<span class="title-subline">%s</span>',
            $title->getSubline()
        ));
    }
    
    echo sprintf('<div class="title-wrapper-%s">', $title->getBaseClass());
    
    // add right-floated elements next to the title
    if($title->hasContextElements())
    {
        $elements = array_map('toString', $title->getContextElements());

        echo sprintf(
            '<div class="title-right">%s</div>',
            implode(' ', $elements)
        );
    }
    
    echo sprintf(
        '<%1$s class="%2$s">%3$s</%1$s>',
        $title->getTagName(),
        $title->classesToString(),
        $string->render()
    );
    
    echo '</div>';