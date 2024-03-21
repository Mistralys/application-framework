<?php

declare(strict_types=1);

namespace UI\Targets;

use AppUtils\HTMLTag;
use UI\AdminURLs\AdminURL;

class URLTarget extends BaseTarget
{
    private string $url;
    private ?string $target = null;

    /**
     * @param string|AdminURL $url
     */
    public function __construct($url)
    {
        $this->url = (string)$url;
    }

    /**
     * @param string|AdminURL $url
     * @param bool $newTab
     * @return self
     */
    public static function create($url, bool $newTab) : self
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