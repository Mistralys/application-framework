<?php

declare(strict_types=1);

namespace Application\MarkdownRenderer;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableTrait;

abstract class BaseCustomTag implements RenderableInterface
{
    use RenderableTrait;

    protected AttributeCollection $params;
    protected string $matchedText;
    private static int $tagCounter = 0;
    private int $number;

    public function __construct(string $matchedText, AttributeCollection $params)
    {
        self::$tagCounter++;

        $this->number = self::$tagCounter;
        $this->matchedText = $matchedText;
        $this->params = $params;
    }

    public function getAttributes() : AttributeCollection
    {
        return $this->params;
    }

    public function getMatchedText(): string
    {
        return $this->matchedText;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getPlaceholder() : string
    {
        return sprintf('999%016d999', $this->getNumber());
    }

    public function getAttribute(string $name) : string
    {
        return $this->params->getAttribute($name);
    }
}
