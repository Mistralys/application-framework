<?php

declare(strict_types=1);

namespace DBHelper\Exception;

use DBHelper;

class HTMLErrorRenderer extends BaseErrorRenderer
{
    public function getEmptyMessageText(): string
    {
        return '<i>No message specified</i>';
    }

    protected function nl(): self
    {
        $this->message->nl();
        return $this;
    }

    protected function line(string $content) : self
    {
        $this->message->add($content)->nl();
        return $this;
    }

    protected function styleError(string $text): string
    {
        return '<i class="text-error">'.$text.'</i>';
    }

    protected function renderSQL(): string
    {
        return DBHelper::getSQLHighlighted();
    }
}
