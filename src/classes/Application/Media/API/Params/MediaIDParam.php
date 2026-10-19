<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API\Params;

use Application\AppFactory;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\Collection\MediaRecord;
use Application\API\Parameters\Type\IntegerParameter;

/**
 * The `mediaID` parameter.
 *
 * > NOTE: Unlike {@see \Application\Countries\API\Params\AppCountryIDParam},
 * > this deliberately uses neither `SelectableValueParamTrait` nor
 * > `validateByValueExistsCallback()`. Live installs hold 10,000+ media
 * > documents, which must not be enumerated for a selectable-value list, and
 * > an exists-callback failure would answer an unknown ID with the generic
 * > `183401` before {@see \Application\Media\API\MediaDocumentParamsContainer::requireValue()}
 * > can send the domain 404.
 *
 * @package Media
 * @subpackage API
 */
class MediaIDParam extends IntegerParameter
{
    public function __construct()
    {
        parent::__construct(
            MediaDocumentAPIInterface::PARAM_MEDIA_ID,
            'Media document ID'
        );

        $this->setDescription('ID of the media document.');
    }

    public function getMediaID() : ?int
    {
        return $this->getValue();
    }

    public function getMediaRecord() : ?MediaRecord
    {
        $id = $this->getMediaID();

        if($id === null) {
            return null;
        }

        $collection = AppFactory::createMediaCollection();

        if($collection->idExists($id)) {
            return $collection->getByID($id);
        }

        return null;
    }
}
