<?php
/**
 * @package User Interface
 * @subpackage Interfaces
 */

declare(strict_types=1);

namespace UI\Interfaces;

use UI\Traits\ButtonLayoutTrait;

/**
 * Interface for the available button layouts.
 *
 * @package User Interface
 * @subpackage Interfaces
 *
 * @see ButtonLayoutTrait
 */
interface ButtonLayoutInterface extends ActivatableInterface
{
    public const LAYOUT_DEFAULT = 'default';
    public const LAYOUT_DEVELOPER = 'developer';
    public const LAYOUT_WARNING = 'warning';
    public const LAYOUT_INVERSE = 'inverse';
    public const LAYOUT_SUCCESS = 'success';
    public const LAYOUT_INFO = 'info';
    public const LAYOUT_DANGER = 'danger';
    public const LAYOUT_PRIMARY = 'primary';
    public const LAYOUT_LINK = 'link';

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous() : self;

    /**
     * @return $this
     */
    public function makePrimary() : self;

    /**
     * @return $this
     */
    public function makeSuccess() : self;

    /**
     * @return $this
     */
    public function makeDeveloper() : self;

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
    public function makeInverse() : self;

    /**
     * Sets the button's layout to the specified type.
     *
     * @param string $layoutID
     * @return $this
     */
    public function makeLayout(string $layoutID) : self;

    /**
     * Sets the button's layout when it is active.
     *
     * @param string $layoutID
     * @return $this
     */
    public function makeActiveLayout(string $layoutID) : self;
}
