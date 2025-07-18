<?php
/**
 * @package User Interface
 * @subpackage Form Elements
 */

declare(strict_types=1);

namespace UI\Form;

use UI;

/**
 * Interface for custom form elements.
 *
 * Custom form elements are used to extend the framework's
 * form capabilities with custom HTML elements that are not
 * provided by HTML QuickForm.
 *
 * @package User Interface
 * @subpackage Form Elements
 */
interface CustomElementInterface
{
    public static function getElementTypeID(): string;
    public static function getElementTypeLabel() : string;

    /**
     * Whether the element is in demo mode.
     *
     * This is used in the test application screen that showcases
     * all custom elements. It allows them to autoconfigure themselves
     * with example data as necessary.
     *
     * It is enabled by setting the runtime property
     * {@see \UI_Form::PROPERTY_DEMO_MODE} to `true`.
     *
     * @return bool
     */
    public function isDemoMode() : bool;

    public function getUI() : UI;
}
