<?php
/**
 * @package User Interface
 * @subpackage Form Elements
 */

declare(strict_types=1);

namespace UI\Form;

use UI;
use UI_Form;

/**
 * Trait used to implement common custom form element methods
 * that implement the interface {@see CustomElementInterface}.
 *
 * @package User Interface
 * @subpackage Form Elements
 */
trait CustomElementTrait
{
    public function isDemoMode() : bool
    {
        return $this->getRuntimeProperty(UI_Form::PROPERTY_DEMO_MODE) === true;
    }

    public function getUI() : UI
    {
        return UI::getInstance();
    }
}
