<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

/**
 * @see TaggableInterface
 */
trait TaggableTrait
{
    private ?Taggable $tagger = null;

    public function getTagManager() : Taggable
    {
        if(!isset($this->tagger)) {
            $this->tagger = new Taggable(
                $this->getTagCollection(),
                $this->getTagRecordPrimaryValue()
            );
        }

        return $this->tagger;
    }

    public function getTagConnector(): TagConnector
    {
        return $this->getTagCollection()->getTagConnector();
    }
}
