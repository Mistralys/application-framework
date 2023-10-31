<?php

declare(strict_types=1);

namespace Application;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;
use League\CommonMark\CommonMarkConverter;

class MarkdownRenderer implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_HTML_INPUT = 'html_input';
    public const OPTION_ALLOW_UNSAFE_LINKS = 'allow_unsafe_links';

    public const HTML_MODE_ALLOW = 'allow';
    public const HTML_MODE_STRIP = 'strip';
    public const HTML_MODE_ESCAPE = 'escape';

    private function __construct()
    {
    }

    public static function create() : self
    {
        return new self();
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_HTML_INPUT => self::HTML_MODE_STRIP,
            self::OPTION_ALLOW_UNSAFE_LINKS => false
        );
    }

    public function render(string $markdown) : string
    {
        $parser = new CommonMarkConverter($this->getOptions());

        return (string)$parser->convert($markdown);
    }

    /**
     * @param string $mode
     * @return $this
     *
     * @see self::HTML_MODE_ALLOW
     * @see self::HTML_MODE_STRIP
     * @see self::HTML_MODE_ESCAPE
     */
    public function setHTMLInput(string $mode) : self
    {
        return $this->setOption(self::OPTION_HTML_INPUT, $mode);
    }
}
