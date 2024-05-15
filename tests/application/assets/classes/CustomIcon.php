<?php
/**
* File containing the {@see TestDriver\CustomIcon} class.
*
* @package TestDriver
* @subpackage User Interface
* @see TestDriver\CustomIcon
*
* @template-version 1
*/

declare(strict_types=1);

namespace ;

use UI_Icon;

/**
* Custom icon class for application-specific icons. Extends
* the framework's icon class, so has the capability to both
* overwrite existing icons and to add new ones.
*
* @package TestDriver
* @subpackage User Interface
* @author Sebastian Mordziol <s.mordziol@mistralys.eu>
* @see UI_Icon
*/
class CustomIcon extends UI_Icon
{
    // region: Icon type methods
    
    /**
     * @return $this
     */
    public function planet() : self { return $this->setType('globe-europe', 'fas'); }
    /**
     * @return $this
     */
    public function revisionable() : self { return $this->setType('rev', 'fab'); }
    
    // endregion
}
