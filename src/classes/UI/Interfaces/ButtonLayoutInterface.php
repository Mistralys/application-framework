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
    public const string LAYOUT_DEFAULT = 'default';
    public const string LAYOUT_DEVELOPER = 'developer';
    public const string LAYOUT_WARNING = 'warning';
    public const string LAYOUT_INVERSE = 'inverse';
    public const string LAYOUT_SUCCESS = 'success';
    public const string LAYOUT_INFO = 'info';
    public const string LAYOUT_DANGER = 'danger';
    public const string LAYOUT_PRIMARY = 'primary';
    public const string LAYOUT_LINK = 'link';

    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeDangerous(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makePrimary(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeSuccess(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeDeveloper(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeWarning(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeInfo(bool $enable=true) : self;

    /**
     * @param bool $enable Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeInverse(bool $enable=true) : self;

    /**
     * Sets the button's layout to the specified type.
     *
     * @param string $layoutID
     * @param bool $enabled Can be used as a toggle. If set to false, the layout will not be applied.
     * @return $this
     */
    public function makeLayout(string $layoutID, bool $enabled=true) : self;

    /**
     * Sets the button's layout when it is active.
     *
     * @param string $layoutID
     * @return $this
     */
    public function makeActiveLayout(string $layoutID) : self;
}
