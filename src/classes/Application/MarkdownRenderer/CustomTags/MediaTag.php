<?php

declare(strict_types=1);

namespace Application\MarkdownRenderer\CustomTags;

use Application\AppFactory;
use Application\MarkdownRenderer;
use Application\MarkdownRenderer\BaseCustomTag;
use Application_Media_Document;
use AppUtils\AttributeCollection;
use AppUtils\HTMLTag;

/**
 * Detects media tags:
 *
 * <code>{media: 42}</code>
 * <code>{media: 42 width="400"}</code>
 * <code>{media: 42 title="Optional image title attribute"}</code>
 */
class MediaTag extends BaseCustomTag
{
    private int $mediaID;

    public function __construct(string $matchedText, int $mediaID, AttributeCollection $params)
    {
        $this->mediaID = $mediaID;

        parent::__construct($matchedText, $params);
    }

    public function getMediaID(): int
    {
        return $this->mediaID;
    }

    /**
     * @param string $subject
     * @return MediaTag[]
     */
    public static function findTags(string $subject) : array
    {
        preg_match_all('/{media:\s*([0-9]+)}|{media:\s*([0-9]+) \s*(.*)}/iU', $subject, $matches);

        $result = array();

        foreach($matches[0] as $idx => $matchedText)
        {
            $id = $matches[1][$idx];
            $params = '';

            if($matches[2][$idx] !== '') {
                $id = $matches[2][$idx];
                $params = $matches[3][$idx];
            }

            $result[] = new MediaTag(
                $matchedText,
                (int)$id,
                MarkdownRenderer::parseParams($params)
            );
        }

        return $result;
    }

    public function getDocument() : ?Application_Media_Document
    {
        $id = $this->getMediaID();
        $media = AppFactory::createMedia();

        if($media->idExists($id)) {
            return $media->getByID($id);
        }

        return null;
    }

    public function getWidth() : ?int
    {
        $width = (int)$this->getAttribute('width');
        if($width > 0) {
            return $width;
        }

        return null;
    }

    public function getTitle() : ?string
    {
        $title = trim($this->getAttribute('title'));
        if(!empty($title)) {
            return $title;
        }

        return null;
    }

    public function render(): string
    {
        $document = $this->getDocument();

        if($document === null) {
            return (string)sb()->warning(sb()->bold(
                t('Media %1$s not found.', '#'.$this->getMediaID())
            ));
        }

        $tag = HTMLTag::create('img')
            ->setSelfClosing()
            ->addClass('visual')
            ->attr('src', $document->getThumbnailURL($this->getWidth()))
            ->attr('alt', $document->getName())
            ->attr('title', (string)$this->getTitle());

        return (string)HTMLTag::create('a')
            ->attr('href', $document->getThumbnailURL())
            ->setContent($tag);
    }
}
