<?php

declare(strict_types=1);

namespace UI\Targets;

use AppUtils\HTMLTag;

abstract class BaseTarget
{
    private ?HTMLTag $tag = null;

    public function getLinkTag() : HTMLTag
    {
        if(!isset($this->tag)) {
            $this->tag = $this->createLinkTag();
        }

        return $this->tag;
    }

    abstract protected function createLinkTag() : HTMLTag;
}
