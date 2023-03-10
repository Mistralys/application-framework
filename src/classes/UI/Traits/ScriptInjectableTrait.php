<?php
/**
 * @package UserInterface
 * @subpackage Traits
 * @see \UI\Traits\ScriptInjectableTrait
 */

declare(strict_types=1);

namespace UI\Traits;

use UI;
use UI\ClientResourceCollection;

/**
 * Implementation of the matching interface for any objects
 * that add client resources. Uses a {@see ClientResourceCollection}
 * to keep track of the resources added by the class.
 *
 * @package UserInterface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see ScriptInjectableInterface
 */
trait ScriptInjectableTrait
{
    public function injectUIScripts(UI $ui) : self
    {
        $this->getUIScripts($ui);
        return $this;
    }

    public function getUIScripts(UI $ui) : ClientResourceCollection
    {
        $collection = new ClientResourceCollection($ui->getResourceManager());

        $this->_injectUIScripts($collection);

        return $collection;
    }

    abstract protected function _injectUIScripts(ClientResourceCollection $collection) : void;
}
