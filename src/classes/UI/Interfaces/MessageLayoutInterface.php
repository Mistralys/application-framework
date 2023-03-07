<?php

declare(strict_types=1);

namespace UI\Interfaces;

use UI_Icon;

interface MessageLayoutInterface
{
    /**
     * @return $this
     */
    public function makeDismissable() : self;

    /**
     * @return $this
     */
    public function makeNotDismissable() : self;

    /**
     * @return $this
     */
    public function makeSlimLayout() : self;

    /**
     * @return $this
     */
    public function makeInline() : self;

    /**
     * @return $this
     */
    public function makeError() : self;

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
    public function makeInfo() : self;

    /**
     * @return $this
     */
    public function makeLargeLayout() : self;

    /**
     * @return $this
     */
    public function makeDefaultLayout() : self;

    /**
     * @return $this
     */
    public function enableIcon() : self;

    /**
     * @return $this
     */
    public function disableIcon() : self;

    /**
     * @param UI_Icon $icon
     * @return $this
     */
    public function setCustomIcon(UI_Icon $icon) : self;
}
