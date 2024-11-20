<?php
/**
 * @package User Interface
 * @subpackage Properties Grid
 */

declare(strict_types=1);

namespace UI\PropertiesGrid\Property;

use Application\Tags\Taggables\TaggableInterface;
use UI_PropertiesGrid_Property;
use UI_StringBuilder;

/**
 * Displays all tags of a tabbable object.
 *
 * NOTE: Shown only if the tagging is enabled for the object.
 *
 * @package User Interface
 * @subpackage Properties Grid
 */
class TagsGridProperty extends UI_PropertiesGrid_Property
{
    protected function init(): void
    {
        if($this->text instanceof TaggableInterface) {
            $this->requireTrue($this->text->isTaggingEnabled(), 'Tagging is not enabled for this object');
        }
    }

    protected function filterValue($value): UI_StringBuilder
    {
        if($value instanceof TaggableInterface) {
            return sb()->html($value->getTagManager()->renderTaggingUI());
        }

        return $this->resolveEmptyText();
    }
}
