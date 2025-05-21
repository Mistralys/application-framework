<?php

declare(strict_types=1);

namespace Application\SystemMails\MailContents;

use AppUtils\Interfaces\StringableInterface;
use UI_Exception;

class MailParagraph extends BaseMailContent
{
    protected string $content = '';

    /**
     * @param string|number|StringableInterface|NULL $content
     * @throws UI_Exception
     */
    public function __construct($content)
    {
        $this->content = toString($content);
    }

    public function getContent() : string
    {
        return $this->content;
    }
}
