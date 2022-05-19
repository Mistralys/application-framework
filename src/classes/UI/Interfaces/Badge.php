<?php

interface UI_Interfaces_Badge
{
    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return $this
     */
    public function setLabel($label) : self;

    /**
     * @param string|number|UI_Renderable_Interface|NULL $code
     * @return $this
     */
    public function setWrapper($code) : self;

    /**
     * @return $this
     */
    public function makeDangerous() : self;

    /**
     * @return $this
     */
    public function makeInfo() : self;

    /**
     * @return $this
     */
    public function makeSuccess() : self;

    /**
     * @return $this
     */
    public function makeWarning() : self;

    /**
     * @return $this
     */
    public function makeInverse() : self;

    /**
     * @return $this
     */
    public function makeInactive() : self;

    /**
     * @return $this
     */
    public function cursorHelp() : self;

    /**
     * @return $this
     */
    public function makeLarge() : self;

    public function getLabel() : string;
}
