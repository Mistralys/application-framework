<?php

declare(strict_types=1);

namespace UI\Targets;

use AppUtils\HTMLTag;

class URLTarget extends BaseTarget
{
    private string $url;
    private ?string $target = null;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public static function create(string $url, bool $newTab) : self
    {
        $target = new self($url);

        if($newTab) {
            $target->makeNewTab();
        }

        return $target;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function getTarget() : ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target) : self
    {
        $this->target = $target;
        return $this;
    }

    public function makeNewTab() : self
    {
        return $this->setTarget('_blank');
    }

    protected function createLinkTag() : HTMLTag
    {
        $tag = HTMLTag::create('a')
            ->href($this->url);

        if($this->target !== null) {
            $tag->attr('target', $this->target);
        }

        return $tag;
    }
}