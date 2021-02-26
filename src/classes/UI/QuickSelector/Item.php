<?php

declare(strict_types=1);

class UI_QuickSelector_Item extends UI_QuickSelector_Base
{
    /**
     * @var string
     */
    protected $url;

    public function __construct(UI_QuickSelector $selector, string $id, string $label, string $url)
    {
        parent::__construct($selector, $id, $label);

        $this->url = $url;
    }

    public function render(): string
    {
        $this->ui->addJavascriptHeadStatement(
            sprintf('%s.AddItem', $this->selector->getJSName()),
            $this->id,
            $this->url
        );

        $selected = '';
        $label = $this->label;

        if($this->id === $this->selector->getSelectedID()) {
            $selected = ' selected="selected"';
            $label = '['.$label.']';
        }

        return
            '<option value="'.$this->id.'"'.$selected.'>'.
                $label.
            '</option>';
    }
}
