<?php

declare(strict_types=1);

abstract class UI_QuickSelector_Base
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var UI_QuickSelector
     */
    protected $selector;

    /**
     * @var UI
     */
    protected $ui;

    public function __construct(UI_QuickSelector $selector, string $id, string $label)
    {
        $this->selector = $selector;
        $this->id = $id;
        $this->label = $label;
        $this->ui = $this->selector->getUI();
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return UI_QuickSelector
     */
    public function getSelector(): UI_QuickSelector
    {
        return $this->selector;
    }

    abstract public function render() : string;
}
