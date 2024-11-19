<?php
/**
 * @package Tagging
 * @subpackage AJAX Methods
 */

declare(strict_types=1);

namespace Application\AjaxMethods;

use Application\Ajax\BaseJSONAjaxMethod;
use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggableUniqueID;
use AppUtils\ArrayDataCollection;

/**
 * Fetches information on a taggable record by its unique ID.
 *
 * ## Parameters
 *
 * 1. {@see self::PARAM_UNIQUE_ID} (required) - Unique ID of the taggable record.
 *
 * ## Response
 *
 * JSON object with the following properties:
 *
 * - {@see self::KEY_UNIQUE_ID} - Unique ID of the taggable record.
 * - {@see self::KEY_LABEL} - Label of the taggable record.
 * - {@see self::KEY_TYPE_LABEL} - Label of the taggable record type.
 *
 * @package Tagging
 * @subpackage AJAX Methods
 */
class GetTaggableInfoMethod extends BaseJSONAjaxMethod
{
    public const METHOD_NAME = 'GetTaggableInfo';
    public const PARAM_UNIQUE_ID = 'unique_id';
    public const VALIDATION_TAGGABLE_NOT_FOUND = 167901;
    public const KEY_UNIQUE_ID = 'uniqueID';
    public const KEY_LABEL = 'label';
    public const KEY_TYPE_LABEL = 'typeLabel';
    private TaggableInterface $taggable;
    private TaggableUniqueID $uniqueID;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected function collectPayload(ArrayDataCollection $payload) : void
    {
        $payload->setKey(self::KEY_UNIQUE_ID, $this->uniqueID->getID());
        $payload->setKey(self::KEY_LABEL, $this->taggable->getTaggableLabel());
        $payload->setKey(self::KEY_TYPE_LABEL, $this->taggable->getTagCollection()->getTaggableTypeLabel());
    }

    protected function validateRequest(): void
    {
        $uniqueID = TaggableUniqueID::parse((string)$this->request->getParam(self::PARAM_UNIQUE_ID));

        if(!$uniqueID->isValid()) {
            $this->sendError(
                sprintf(
                    'Invalid unique ID. Reason given: %s',
                    $uniqueID->getErrorMessage()
                ),
                null,
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

        $this->uniqueID = $uniqueID;
        $this->taggable = $uniqueID->getTaggable();
    }
}