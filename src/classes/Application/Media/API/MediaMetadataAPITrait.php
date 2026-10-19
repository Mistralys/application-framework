<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\Parameters\Type\ClearableStringParameter;
use Application\API\Parameters\Type\StringParameter;

/**
 * Trait used to implement the interface {@see MediaMetadataAPIInterface}.
 *
 * It needs no writer and depends on no other piece.
 *
 * @package Media
 * @subpackage API
 * @see MediaMetadataAPIInterface
 */
trait MediaMetadataAPITrait
{
    private ?StringParameter $mediaNameParam = null;
    private StringParameter|ClearableStringParameter|null $mediaDescriptionParam = null;
    private StringParameter|ClearableStringParameter|null $mediaKeywordsParam = null;

    public function registerMediaMetadataParams(bool $clearable) : self
    {
        $this->mediaNameParam = $this->manageParams()
            ->addParam(MediaMetadataAPIInterface::PARAM_NAME, 'Name')
            ->string();

        $this->mediaNameParam
            ->setDescription('Name of the media document. Defaults to the uploaded file\'s base name when not specified.')
            ->setMaxLength(MediaMetadataAPIInterface::MAX_NAME_LENGTH);

        if($clearable) {
            $this->mediaDescriptionParam = $this->manageParams()
                ->addParam(MediaMetadataAPIInterface::PARAM_DESCRIPTION, 'Description')
                ->clearableString();

            $this->mediaKeywordsParam = $this->manageParams()
                ->addParam(MediaMetadataAPIInterface::PARAM_KEYWORDS, 'Keywords')
                ->clearableString();
        } else {
            $this->mediaDescriptionParam = $this->manageParams()
                ->addParam(MediaMetadataAPIInterface::PARAM_DESCRIPTION, 'Description')
                ->string();

            $this->mediaKeywordsParam = $this->manageParams()
                ->addParam(MediaMetadataAPIInterface::PARAM_KEYWORDS, 'Keywords')
                ->string();
        }

        $this->mediaDescriptionParam
            ->setDescription('Free-text description of the media document. Must not contain HTML markup.')
            ->setMaxLength(MediaMetadataAPIInterface::MAX_DESCRIPTION_LENGTH);

        $this->mediaKeywordsParam
            ->setDescription('Comma-separated search keywords for the media document. Must not contain HTML markup.')
            ->setMaxLength(MediaMetadataAPIInterface::MAX_KEYWORDS_LENGTH);

        return $this;
    }

    public function getMediaName() : ?string
    {
        return $this->mediaNameParam?->getValue();
    }

    public function getMediaDescription() : ?string
    {
        return $this->mediaDescriptionParam?->getValue();
    }

    public function getMediaKeywords() : ?string
    {
        return $this->mediaKeywordsParam?->getValue();
    }

    public function resolveMediaName(string $default) : string
    {
        $name = $this->getMediaName();

        if($name !== null && $name !== '') {
            return $name;
        }

        return mb_substr($default, 0, MediaMetadataAPIInterface::MAX_NAME_LENGTH);
    }

    public function validateMediaMetadata() : void
    {
        $this->validateNoHTML($this->getMediaDescription());
        $this->validateNoHTML($this->getMediaKeywords());
    }

    private function validateNoHTML(?string $value) : void
    {
        if($value === null || $value === '') {
            return;
        }

        if(strip_tags($value) === $value) {
            return;
        }

        $this->errorResponse(MediaMetadataAPIInterface::VALIDATION_HTML_NOT_ALLOWED)
            ->setErrorMessage('HTML markup is not allowed in the description or keywords.')
            ->send();
    }
}
