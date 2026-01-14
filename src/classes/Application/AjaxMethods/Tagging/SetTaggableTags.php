<?php
/**
 * @package Tagging
 * @subpackage AJAX Methods
 */

declare(strict_types=1);

namespace Application\AjaxMethods\Tagging;

use Application\Ajax\BaseJSONAjaxMethod;
use Application\AppFactory;
use Application\Tags\Ajax\TaggableAjaxRequestInterface;
use Application\Tags\Ajax\TaggableAjaxRequestTrait;
use Application\Tags\Taggables\TaggableInterface;
use AppUtils\ArrayDataCollection;

/**
 * Connects and disconnects tags to a taggable record.
 *
 * ## Parameters
 *
 * 1. {@see TaggableAjaxRequestInterface::PARAM_UNIQUE_ID} (required) - Unique ID of the taggable record.
 * 2. {@see self::KEY_TAGS} (required) - Array of tags to process. Each must be an array with the keys {@see self::KEY_TAG_ID} and {@see self::KEY_TAG_CONNECTED}.
 *
 * ## Response
 *
 * Empty data response with the usual success status.
 *
 * @package Tagging
 * @subpackage AJAX Methods
 */
class SetTaggableTags extends BaseJSONAjaxMethod implements TaggableAjaxRequestInterface
{
    use TaggableAjaxRequestTrait;

    public const string METHOD_NAME = 'SetTaggableTags';

    public const string KEY_TAGS = 'tags';
    public const string KEY_TAG_ID = 'id';
    public const string KEY_TAG_CONNECTED = 'connected';
    public const int ERROR_INVALID_TAG_DEFINITION = 168201;

    private TaggableInterface $taggable;

    /**
     * @var array<int,array{id:int,connected:bool}>
     */
    private array $tags = array();

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    protected function collectPayload(ArrayDataCollection $payload): void
    {
        $this->startTransaction();

        $manager = $this->taggable->getTagManager();
        $collection = AppFactory::createTags();

        foreach($this->tags as $tagDef) {
            $tag = $collection->getByID($tagDef['id']);
            if($tagDef['connected']) {
                $manager->addTag($tag);
            } else {
                $manager->removeTag($tag);
            }
        }

        $this->endTransaction();
    }

    protected function validateRequest(): void
    {
        $this->taggable = $this->getUniqueID()->getTaggable();

        $tags = (array)$this->request->registerParam(self::KEY_TAGS)->setArray()->get();

        foreach($tags as $tagDef) {
            if(!isset($tagDef[self::KEY_TAG_ID], $tagDef[self::KEY_TAG_CONNECTED])) {
                $this->sendError(
                    'Invalid tag definition',
                    null,
                    self::ERROR_INVALID_TAG_DEFINITION
                );
            }

            $this->tags[] = array(
                'id' => (int)$tagDef[self::KEY_TAG_ID],
                'connected' => string2bool($tagDef[self::KEY_TAG_CONNECTED])
            );
        }
    }
}
