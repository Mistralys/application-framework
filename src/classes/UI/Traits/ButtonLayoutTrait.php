<?php
/**
 * @package User Interface
 * @subpackage Traits
 */

declare(strict_types=1);

namespace UI\Traits;

use UI\Interfaces\ButtonLayoutInterface;

/**
 * Trait to implement the interface {@see ButtonLayoutInterface}.
 *
 * @package User Interface
 * @subpackage Traits
 *
 * @see ButtonLayoutInterface
 */
trait ButtonLayoutTrait
{
    protected string $layout = ButtonLayoutInterface::LAYOUT_DEFAULT;
    protected ?string $activeLayout = null;

    /**
     * @return $this
     */
    public function makeInfo(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_INFO, $enabled);
    }

    /**
     * Styles the button as a success button.
     *
     * @return $this
     */
    public function makeSuccess(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_SUCCESS, $enabled);
    }

    /**
     * Styles the button as a warning button for potentially dangerous operations.
     *
     * @return $this
     */
    public function makeWarning(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_WARNING, $enabled);
    }

    /**
     * Styles the button as an inverted button.
     *
     * @return $this
     */
    public function makeInverse(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_INVERSE, $enabled);
    }

    /**
     * Sets the button's layout to the specified type.
     *
     * @param string $layoutID
     * @param bool $enabled If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeLayout(string $layoutID, bool $enabled=true) : self
    {
        if($enabled) {
            $this->layout = $layoutID;
        }

        return $this;
    }

    /**
     * Sets the button's layout when it is active.
     *
     * @param string $layoutID
     * @return $this
     */
    public function makeActiveLayout(string $layoutID) : self
    {
        $this->activeLayout = $layoutID;
        return $this;
    }

    /**
     * Styles the button as a primary button.
     *
     * @return $this
     */
    public function makePrimary(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_PRIMARY, $enabled);
    }

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_DANGER, $enabled);
    }

    /**
     * Styles the button for developers.
     *
     * @return $this
     */
    public function makeDeveloper(bool $enabled=true) : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_DEVELOPER, $enabled);
    }

    /**
     * Determines the layout ID to use depending on the button's state.
     * @return string
     */
    protected function resolveLayout() : string
    {
        if(isset($this->activeLayout) && $this->isActive()) {
            return $this->activeLayout;
        }

        return $this->layout;
    }
}
