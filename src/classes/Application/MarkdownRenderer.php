<?php

declare(strict_types=1);

namespace Application;

use Application\MarkdownRenderer\BaseCustomTag;
use Application\MarkdownRenderer\CustomTags\MediaTag;
use AppUtils\AttributeCollection;
use AppUtils\ConvertHelper;
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

    /**
     * @var BaseCustomTag[]
     */
    private array $tags = array();

    public function render(string $markdown) : string
    {
        $this->tags = array();

        $markdown = $this->preParse($markdown);

        $parser = new CommonMarkConverter($this->getOptions());

        $markdown = (string)$parser->convert($markdown);

        return $this->postParse($markdown);
    }

    private function preParse(string $markdown) : string
    {
        array_push($this->tags, ...MediaTag::findTags($markdown));

        foreach($this->tags as $tag)
        {
            $markdown = str_replace($tag->getMatchedText(), $tag->getPlaceholder(), $markdown);
        }

        return $markdown;
    }

    public static function parseParams(string $params) : AttributeCollection
    {
        $result = AttributeCollection::create();
        $params = trim($params);
        if($params === '') {
            return $result;
        }

        $escaped = array(
            '\"' => '__ESCAPED_DBL_QUOTE__',
        );

        $restored = array(
            '__ESCAPED_DBL_QUOTE__' => '"',
        );

        $params = str_replace(array_keys($escaped), array_values($escaped), $params);
        $parts = ConvertHelper::explodeTrim(' ', $params);

        foreach($parts as $part)
        {
            preg_match('/([a-z0-9]+)\s*=\s*"([^"]+)"/', $part, $partMatches);

            if(!empty($partMatches[0])) {
                $result->attr($partMatches[1], str_replace(array_keys($restored), array_values($restored), $partMatches[2]));
            }
        }

        return $result;
    }

    private function postParse(string $markdown) : string
    {
        foreach($this->tags as $tag)
        {
            $markdown = str_replace($tag->getPlaceholder(), $tag->render(), $markdown);
        }

        $this->tags = array();

        return $markdown;
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
