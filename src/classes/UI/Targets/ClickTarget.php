<?php

declare(strict_types=1);

namespace UI\Targets;

use AppUtils\HTMLTag;
use JSHelper;

class ClickTarget extends BaseTarget
{
    private string $statement;

    public function __construct(string $statement)
    {
        $this->statement = $statement;
    }

    public static function create(string $statement) : self
    {
        return new self($statement);
    }

    public function getStatement() : string
    {
        return $this->statement;
    }

    public function setStatement(string $statement) : self
    {
        $this->statement = $statement;
        return $this;
    }

    protected function createLinkTag(): HTMLTag
    {
        return HTMLTag::create('a')
            ->href('#')
            ->attr('onclick', $this->renderStatement().'; return false;');
    }

    private function renderStatement() : string
    {
        return rtrim(JSHelper::quoteStyle($this->statement)->doubleToSingle(), ';');
    }
}
