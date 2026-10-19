<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

/**
 * Trait used to implement the interface {@see MediaDocumentAPIInterface}.
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentAPIInterface
 */
trait MediaDocumentAPITrait
{
    private ?MediaDocumentParamsContainer $mediaDocumentParamsContainer = null;

    public function manageMediaDocumentParams() : MediaDocumentParamsContainer
    {
        if(!isset($this->mediaDocumentParamsContainer)) {
            $this->mediaDocumentParamsContainer = new MediaDocumentParamsContainer($this);
        }

        return $this->mediaDocumentParamsContainer;
    }
}
