<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer;
use Application\Media\API\Params\MediaIDHandler;
use Application\Media\Collection\MediaRecord;

/**
 * Container for the media document lookup parameter handlers.
 *
 * Resolves a single {@see MediaRecord} from the `mediaID` parameter. Its
 * `requireValue()` is overridden (the {@see \Maileditor\Mails\API\MailParamsContainer}
 * shape, from the HCP Editor) to send the domain
 * {@see MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID} (681201)
 * with HTTP 404 — never the generic `ERROR_NO_VALUE_AVAILABLE` (183004).
 *
 * @package Media
 * @subpackage API
 *
 * @method MediaDocumentAPIInterface getMethod()
 */
class MediaDocumentParamsContainer extends BaseParamsHandlerContainer
{
    public function __construct(MediaDocumentAPIInterface $method)
    {
        parent::__construct($method);
    }

    public function resolveValue() : ?MediaRecord
    {
        $value = parent::resolveValue();

        if($value instanceof MediaRecord) {
            return $value;
        }

        return null;
    }

    public function requireValue() : MediaRecord
    {
        $record = $this->resolveValue();

        if($record instanceof MediaRecord) {
            return $record;
        }

        $this->getMethod()->errorResponse(MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID)
            ->makeNotFound()
            ->setErrorMessage('The specified media document could not be found.')
            ->send();
    }

    /**
     * Pre-selects a media record, bypassing parameter resolution. Useful
     * when a caller already holds the record (e.g. a chained
     * `processReturn()` call).
     *
     * @param MediaRecord $record
     * @return $this
     */
    public function selectMediaDocument(MediaRecord $record) : self
    {
        return $this->selectValue($record);
    }

    protected function isValidValueType(float|object|array|bool|int|string $value) : bool
    {
        return $value instanceof MediaRecord;
    }

    private ?MediaIDHandler $mediaIDHandler = null;

    public function manageID() : MediaIDHandler
    {
        if(!isset($this->mediaIDHandler)) {
            $this->mediaIDHandler = new MediaIDHandler($this->getMethod());
            $this->registerHandler($this->mediaIDHandler);
        }

        return $this->mediaIDHandler;
    }
}
