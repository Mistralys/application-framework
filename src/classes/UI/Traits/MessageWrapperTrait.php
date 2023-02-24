<?php

declare(strict_types=1);

namespace UI\Traits;

use UI_Icon;

trait MessageWrapperTrait
{
    /**
     * @return $this
     */
    public function makeDismissable() : self
    {
        $this->getMessage()->makeDismissable();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeNotDismissable() : self
    {
        $this->getMessage()->makeNotDismissable();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSlimLayout() : self
    {
        $this->getMessage()->makeSlimLayout();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeInline() : self
    {
        $this->getMessage()->makeInline();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeError() : self
    {
        $this->getMessage()->makeError();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSuccess() : self
    {
        $this->getMessage()->makeSuccess();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeWarning() : self
    {
        $this->getMessage()->makeWarning();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeInfo() : self
    {
        $this->getMessage()->makeInfo();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeLargeLayout() : self
    {
        $this->getMessage()->makeLargeLayout();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeDefaultLayout() : self
    {
        $this->getMessage()->makeDefaultLayout();
        return $this;
    }

    /**
     * @return $this
     */
    public function enableIcon() : self
    {
        $this->getMessage()->enableIcon();
        return $this;
    }

    /**
     * @return $this
     */
    public function disableIcon() : self
    {
        $this->getMessage()->disableIcon();
        return $this;
    }

    /**
     * @return $this
     */
    public function setCustomIcon(UI_Icon $icon) : self
    {
        $this->getMessage()->setCustomIcon($icon);
        return $this;
    }
}
