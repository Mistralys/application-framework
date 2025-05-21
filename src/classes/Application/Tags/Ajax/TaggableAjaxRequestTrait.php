<?php
/**
 * @package Tagging
 * @subpackage Ajax Methods
 */

declare(strict_types=1);

namespace Application\Tags\Ajax;

use Application\Tags\Taggables\TaggableUniqueID;

/**
 * Trait used to handle taggable AJAX requests,
 * with methods to help access the required information
 * from the request.
 *
 * ## Usage
 *
 * 1. Use this trait.
 * 2. Implement the interface {@see TaggableAjaxRequestInterface}.
 * 3. Use the {@see self::getUniqueID()} method to get the unique ID.
 *
 * @package Tagging
 * @subpackage Ajax Methods
 * @see TaggableAjaxRequestInterface
 */
trait TaggableAjaxRequestTrait
{
    public function getUniqueID() : TaggableUniqueID
    {
        $uniqueID = TaggableUniqueID::parse((string)$this->request->getParam(self::PARAM_UNIQUE_ID));

        if(!$uniqueID->isValid()) {
            $this->sendError(
                sprintf(
                    'Invalid unique ID. Reason given: %s',
                    $uniqueID->getErrorMessage()
                ),
                array(
                    'uniqueID' => $uniqueID->getCode()
                ),
                $uniqueID->getCode()
            );
        }

        if(!$uniqueID->taggableExists()) {
            $this->sendError(
                'No taggable not found for the given unique ID.',
                null,
                self::VALIDATION_TAGGABLE_NOT_FOUND
            );
        }

        return $uniqueID;
    }
}
