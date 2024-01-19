<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

trait TaggableTrait
{
    private ?Taggable $tagger = null;

    public function getTagger() : Taggable
    {
        if(!isset($this->tagger)) {
            $this->tagger = new Taggable(
                $this->getTaggingCollection(),
                $this->getTaggingPrimaryKey()
            );
        }

        return $this->tagger;
    }
}
