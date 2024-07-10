<?php

declare(strict_types=1);

namespace Application\SystemMails\MailContents;

use AppUtils\Interfaces\StringableInterface;
use UI_Exception;

class MailButton extends BaseMailContent
{
    private string $label;
    private string $url;

    /**
     * @param string|number|StringableInterface $label
     * @param string $url
     * @throws UI_Exception
     */
    public function __construct($label, string $url)
    {
        $this->label = toString($label);
        $this->url = $url;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getURL() : string
    {
        return $this->url;
    }
}