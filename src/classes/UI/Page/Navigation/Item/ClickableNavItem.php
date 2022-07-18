<?php

declare(strict_types=1);

use AppUtils\AttributeCollection;

class UI_Page_Navigation_Item_Clickable extends UI_Page_Navigation_Item
{
    private string $jsStatement;

    public function __construct(UI_Page_Navigation $nav, string $id, $title, string $jsStatement)
    {
        parent::__construct($nav, $id);

        $this->setTitle($title);
        $this->setJSStatement($jsStatement);
    }

    public function setJSStatement(string $statement) : self
    {
        $this->jsStatement = $statement;
        return $this;
    }

    public function getType() : string
    {
        return 'clickable';
    }

    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attribs = AttributeCollection::create($attributes)
            ->attr('onclick', $this->jsStatement.';return false;')
            ->addClasses($this->getClasses())
            ->href('#');

        if(isset($this->tooltipInfo)) {
            $this->tooltipInfo->injectAttributes($attribs);
        }

        return sprintf(
            '<a%s>%s</a>',
            $attribs,
            $this->renderIconLabel($this->getTitle())
        );
    }
}
