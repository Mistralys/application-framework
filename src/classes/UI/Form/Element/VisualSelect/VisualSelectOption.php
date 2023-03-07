<?php

declare(strict_types=1);

namespace UI\Form\Element\VisualSelect;

use HTML\QuickForm2\Element\Select\SelectOption;

class VisualSelectOption extends SelectOption
{
    public const ATTRIBUTE_IMAGE_URL = 'image-url';
    public const ATTRIBUTE_PLEASE_SELECT = 'data-please-select';

    public function getImageURL() : string
    {
        return (string)$this->getAttribute(self::ATTRIBUTE_IMAGE_URL);
    }

    public function setImageURL(string $url) : self
    {
        return $this->setAttribute(self::ATTRIBUTE_IMAGE_URL, $url);
    }

    public function hasImage() : bool
    {
        return $this->getImageURL() !== '';
    }

    public function isPleaseSelect() : bool
    {
        return $this->getAttribute(self::ATTRIBUTE_PLEASE_SELECT) === 'yes';
    }

    public function hasImageSet() : bool
    {
        return $this->getImageSetID() !== null;
    }

    public function getImageSetID() : ?string
    {
        return $this->getAttribute(ImageSet::ATTRIBUTE_SET_ID);
    }
}
