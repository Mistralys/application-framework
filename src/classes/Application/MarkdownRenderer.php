<?php

declare(strict_types=1);

namespace Application;

use Application\MarkdownRenderer\BaseCustomTag;
use Application\MarkdownRenderer\CustomTags\APIMethodDocTag;
use Application\MarkdownRenderer\CustomTags\MediaTag;
use AppUtils\AttributeCollection;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\StringBuilder;
use AppUtils\Traits\OptionableTrait;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use UI;

class MarkdownRenderer implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_HTML_INPUT = 'html_input';
    public const OPTION_ALLOW_UNSAFE_LINKS = 'allow_unsafe_links';

    public const HTML_MODE_ALLOW = 'allow';
    public const HTML_MODE_STRIP = 'strip';
    public const HTML_MODE_ESCAPE = 'escape';

    public const WRAPPER_CLASS = 'markdown';
    public const WRAPPER_TAG_OPEN = '<div class="'.self::WRAPPER_CLASS.'">';
    public const WRAPPER_TAG_CLOSE = '</div>';
    public const string MARKDOWN_DOCUMENTATION_URL = 'https://commonmark.org/help/';
    public const string MARKDOWN_LANGUAGE_NAME = 'Markdown';

    private function __construct()
    {
    }

    public static function create() : self
    {
        return new self();
    }

    public static function getName() : string
    {
        return 'Markdown';
    }

    public static function injectReference(?StringBuilder $comment=null, bool $quickRef=false) : StringBuilder
    {
        if($comment === null) {
            $comment = sb();
        }

        $comment->t('It is possible to use %1$s syntax.', self::getName());

        if(!$quickRef) {
            return $comment;
        }

        return $comment
            ->nl()
            ->t('Quick reference:')
            ->ul(array(
                sb()->mono('*'.t('Bold text').'*'),
                sb()->mono('_'.t('Italic text').'_'),
                sb()->mono('`'.t('Inline code').'`'),
                sb()->mono('['.t('Link label').'](https://mistralys.eu)'),
                sb()->mono('{image:filename.png}')->muted('('.t('Store image in theme image subfolder %1$s', sb()->code('whatsnew')).')'),
                sb()->mono('{image 20%:filename.png}')->muted('('.t('With percentage width').')'),
                sb()->mono('{image 120px:filename.png}')->muted('('.t('With pixel width').')'),
                sb()->mono('### '.t('Heading'))->muted('('.t('Amount of hashes = level').')'),
                sb()->mono('```')->nl()->mono(t('Code fence'))->nl()->mono('```'),
            ));
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_HTML_INPUT => self::HTML_MODE_STRIP,
            self::OPTION_ALLOW_UNSAFE_LINKS => false,
            'table_of_contents' => array(
                'position' => 'placeholder',
                'placeholder' => '{TOC}',
                'max_heading_level' => 4
            )
        );
    }

    /**
     * @var BaseCustomTag[]
     */
    private array $tags = array();

    public function render(string $markdown) : string
    {
        UI::getInstance()->addStylesheet('ui-markdown.css');

        $this->tags = array();

        $markdown = $this->preParse($markdown);

        $markdownEnv = new Environment($this->getOptions());
        $markdownEnv->addExtension(new CommonMarkCoreExtension());
        $markdownEnv->addExtension(new GithubFlavoredMarkdownExtension());
        $markdownEnv->addExtension(new HeadingPermalinkExtension());
        $markdownEnv->addExtension(new TableExtension());
        $markdownEnv->addExtension(new TableOfContentsExtension());

        $parser = new MarkdownConverter($markdownEnv);

        $markdown = (string)$parser->convert($markdown);

        if(!$this->useWrapper) {
            return $this->postParse($markdown);
        }

        return self::WRAPPER_TAG_OPEN .$this->postParse($markdown). self::WRAPPER_TAG_CLOSE;
    }

    private function preParse(string $markdown) : string
    {
        array_push($this->tags, ...MediaTag::findTags($markdown));
        array_push($this->tags, ...APIMethodDocTag::findTags($markdown));

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

        return str_replace('<table>', '<table class="table table-condensed table-bordered table-hover markdown-table">', $markdown);
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

    private bool $useWrapper = true;

    /**
     * By default, rendering Markdown will return a paragraph-wrapped HTML string.
     * This method will render the given markdown string without the paragraph tags.
     *
     * @param string $getDescription
     * @return string
     */
    public function renderInline(string $getDescription) : string
    {
        $prev = $this->useWrapper;
        $this->useWrapper = false;

        $html = trim($this->render($getDescription));

        // Use regex to reliably strip a single surrounding <p>...</p> block
        $pattern = '/^<p(?:\s[^>]*)?>(.*?)<\/p>$/is';
        if (preg_match($pattern, $html, $matches)) {
            $html = $matches[1];
        }

        $this->useWrapper = $prev;

        return trim($html);
    }
}
