<?php
/**
 * @package UserInterface
 * @subpackage Traits
 * @see \UI\Traits\ScriptInjectableInterface
 */

declare(strict_types=1);

namespace UI\Traits;

use UI;
use UI\ClientResourceCollection;

/**
 * Interface for all objects that have client resources
 * to inject into a UI instance.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableTrait
 */
interface ScriptInjectableInterface
{
    /**
     * @param UI $ui
     * @return $this
     */
    public function injectUIScripts(UI $ui) : self;

    /**
     * @param UI $ui
     * @return ClientResourceCollection
     */
    public function getUIScripts(UI $ui) : ClientResourceCollection;
}
