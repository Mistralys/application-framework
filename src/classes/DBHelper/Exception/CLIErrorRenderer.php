<?php

declare(strict_types=1);

namespace DBHelper\Exception;

use DBHelper;

class CLIErrorRenderer extends BaseErrorRenderer
{
    public function getEmptyMessageText(): string
    {
        return 'No message specified';
    }

    protected function nl(): self
    {
        $this->message->eol();
        return $this;
    }

    protected function line(string $content) : self
    {
        $this->message->add($content)->eol();
        return $this;
    }

    protected function styleError(string $text): string
    {
        return $text;
    }

    protected function renderSQL(): string
    {
        return DBHelper::getSQL();
    }
}
