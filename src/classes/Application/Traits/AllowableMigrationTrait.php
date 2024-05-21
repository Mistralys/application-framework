<?php
/**
 * @package Application
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\Traits;

use Application\Interfaces\AllowableInterface;

/**
 * Temporary trait used to prepare applications for the
 * upcoming interface, {@see AllowableInterface}.
 *
 * For now, the interface methods {@see self::getRequiredRights()}
 * and {@see getFeatureRights()} are not implemented in the interface,
 * but this trait makes it possible to use them beforehand.
 *
 * Screens that use this trait will only have to remove the trait
 * once the interface is implemented.
 *
 * @package Application
 * @subpackage Traits
 * @see AllowableInterface
 */
trait AllowableMigrationTrait
{
    /**
     * @return string|string[] Single right name, or list of right names.
     */
    abstract public function getRequiredRights();

    public function getFeatureRights() : array
    {
        return array();
    }

    public function isUserAllowed() : bool
    {
        $user = $this->getUser();
        $rights = $this->getRequiredRights();
        if(is_string($rights)) {
            $rights = array($rights);
        }

        foreach($rights as $right) {
            if(!$user->hasRight($right)) {
                return false;
            }
        }

        return true;
    }
}
