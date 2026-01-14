<?php
/**
 * @package Tagging
 * @subpackage AJAX Methods
 */

declare(strict_types=1);

namespace Application\AjaxMethods\Tagging;

use Application\Ajax\BaseJSONAjaxMethod;
use Application\Tags\Ajax\TaggableAjaxRequestInterface;
use Application\Tags\Ajax\TaggableAjaxRequestTrait;
use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggableUniqueID;
use Application\Tags\TagRecord;
use AppUtils\ArrayDataCollection;

/**
 * Fetches information on a taggable record by its unique ID.
 *
 * ## Parameters
 *
 * 1. {@see TaggableAjaxRequestInterface::PARAM_UNIQUE_ID} (required) - Unique ID of the taggable record.
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
class GetTaggableInfoMethod extends BaseJSONAjaxMethod implements TaggableAjaxRequestInterface
{
    use TaggableAjaxRequestTrait;

    public const string METHOD_NAME = 'GetTaggableInfo';
    public const string KEY_UNIQUE_ID = 'uniqueID';
    public const string KEY_LABEL = 'label';
    public const string KEY_TYPE_LABEL = 'typeLabel';
    public const string KEY_TAGS = 'tags';
    private TaggableInterface $taggable;
    private TaggableUniqueID $uniqueID;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected function collectPayload(ArrayDataCollection $payload) : void
    {
        $payload->setKey(self::KEY_UNIQUE_ID, $this->uniqueID->getUniqueID());
        $payload->setKey(self::KEY_LABEL, $this->taggable->getTaggableLabel());
        $payload->setKey(self::KEY_TYPE_LABEL, $this->taggable->getTagCollection()->getTaggableTypeLabel());
        $payload->setKey(self::KEY_TAGS, $this->collectTags());
    }

    private function collectTags() : array
    {
        $result = array();

        $this->collectTagsRecursive($result, $this->taggable->getTagCollection()->getRootTag());

        return $result[0]['subTags'];
    }

    private function collectTagsRecursive(array &$list, TagRecord $tag) : void
    {
        $data = $this->collectTag($tag);
        $data['subTags'] = array();

        foreach($tag->getSubTags() as $child) {
            $this->collectTagsRecursive($data['subTags'], $child);
        }

        $list[] = $data;
    }

    private function collectTag(TagRecord $tag) : array
    {
        return array(
            'id' => $tag->getID(),
            'label' => $tag->getLabel(),
            'connected' => $this->taggable->getTagManager()->hasTag($tag)
        );
    }

    protected function validateRequest(): void
    {
        $this->uniqueID = $this->getUniqueID();
        $this->taggable = $this->uniqueID->getTaggable();
    }
}
