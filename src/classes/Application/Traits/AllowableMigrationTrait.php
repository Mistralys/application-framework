<?php
/**
 * @package Application
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\Traits;

use Application\Interfaces\AllowableInterface;
use Application\Interfaces\AllowableMigrationInterface;

/**
 * Temporary trait used to prepare applications for the
 * upcoming interface, {@see AllowableInterface}.
 *
 * For now, the interface methods {@see self::getRequiredRight()}
 * and {@see getFeatureRights()} are not implemented in the interface,
 * but this trait makes it possible to use them beforehand.
 *
 * Screens that use this trait will only have to remove the trait
 * once the interface is implemented.
 *
 * @package Application
 * @subpackage Traits
 * @see AllowableMigrationInterface
 */
trait AllowableMigrationTrait
{
    abstract public function getRequiredRight() : ?string;

    /**
     * @return array<string,string> Human-readable feature label > Right name pairs.
     */
    public function getFeatureRights() : array
    {
        return array();
    }

    public function isUserAllowed() : bool
    {
        $right = $this->getRequiredRight();

        if(!empty($right)) {
            return $this->getUser()->can($right);
        }

        return true;
    }
}
