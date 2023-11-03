<?php

declare(strict_types=1);

namespace Application\Media;

use Application_Media_DocumentInterface;
use AppUtils\HTMLTag;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

class ThumbnailRenderer implements RenderableInterface
{
    use RenderableTrait;

    private Application_Media_DocumentInterface $document;
    private ?int $preferredSize = null;

    public function __construct(Application_Media_DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * @param int|null $size
     * @return $this
     */
    public function setPreferredSize(?int $size) : self
    {
        $this->preferredSize = $size;
        return $this;
    }

    /**
     * Renders a thumbnail image HTML tag for the document.
     *
     * @return string
     */
    public function render() : string
    {
        // Get a thumbnail size adapted to the document type.
        $imageSize = $this->document->getThumbnailDefaultSize($this->preferredSize);

        $img = HTMLTag::create('img')
            ->setEmptyAllowed()
            ->attr('src', $this->document->getThumbnailURL($imageSize))
            ->attr('width', (string)$imageSize)
            ->attr('alt', '')
            ->attr('title', $this->document->getName());

        return (string)sb()->link(
            (string)$img,
            $this->document->getThumbnailURL()
        );
    }
}
