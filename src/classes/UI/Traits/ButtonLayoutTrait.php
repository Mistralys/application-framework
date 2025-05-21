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
    public function makeInfo() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_INFO);
    }

    /**
     * Styles the button as a success button.
     *
     * @return $this
     */
    public function makeSuccess() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_SUCCESS);
    }

    /**
     * Styles the button as a warning button for potentially dangerous operations.
     *
     * @return $this
     */
    public function makeWarning() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_WARNING);
    }

    /**
     * Styles the button as an inverted button.
     *
     * @return $this
     */
    public function makeInverse() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_INVERSE);
    }

    /**
     * Sets the button's layout to the specified type.
     *
     * @param string $layoutID
     * @return $this
     */
    public function makeLayout(string $layoutID) : self
    {
        $this->layout = $layoutID;

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
    public function makePrimary() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_PRIMARY);
    }

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_DANGER);
    }

    /**
     * Styles the button for developers.
     *
     * @return $this
     */
    public function makeDeveloper() : self
    {
        return $this->makeLayout(ButtonLayoutInterface::LAYOUT_DEVELOPER);
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
