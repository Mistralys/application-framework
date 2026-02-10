<?php
/**
 * @package Application
 * @subpackage WhatsNew
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion;

use Application\WhatsNew\WhatsNew;
use Application_Driver;
use AppUtils\AttributeCollection;
use AppUtils\ConvertHelper;
use AppUtils\HTMLTag;
use SimpleXMLElement;
use function AppUtils\parseNumber;
use const APP_URL;

/**
 * Container for a single entry in a what's new file.
 *
 * Path: whatsnew.version.language.item
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class CategoryItem
{
    protected LanguageCategory $category;
    protected string $author = '';
    protected string $issue = '';
    protected string $rawText = '';
    protected string $text;
    protected int $number;

    public function __construct(LanguageCategory $category, int $itemNumber, ?SimpleXMLElement $node)
    {
        $this->number = $itemNumber;
        $this->category = $category;

        if($node !== null) {
            $this->parse($node);
        }
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getLanguage() : VersionLanguage
    {
        return $this->getCategory()->getLanguage();
    }

    public function getCategory(): LanguageCategory
    {
        return $this->category;
    }

    public function getWhatsNew() : WhatsNew
    {
        return $this->category->getWhatsNew();
    }

    protected function parse(SimpleXMLElement $node) : void
    {
        if (isset($node['author']))
        {
            $this->setAuthor((string)$node['author']);
        }

        if (isset($node['issue']))
        {
            $this->setIssue((string)$node['issue']);
        }

        $this->rawText = (string)$node;
    }

    public function getRawText() : string
    {
        return $this->rawText;
    }

    public function getFormText() : string
    {
        // Remove all indentation in the text
        $lines = ConvertHelper::explodeTrim("\n", $this->getRawText());

        return implode(PHP_EOL, $lines);
    }

    public function getText() : string
    {
        if (!isset($this->text))
        {
            $this->renderText();
        }

        return $this->text;
    }

    protected function renderText() : void
    {
        $lines = explode("\n", $this->rawText);

        $keep = array();
        $total = count($lines);
        for ($i = 0; $i < $total; $i++)
        {
            $line = trim($lines[$i]);
            $prev = null;
            $next = null;

            if (isset($lines[($i - 1)]))
            {
                $prev = $lines[($i - 1)];
            }

            if (isset($lines[($i + 1)]))
            {
                $next = $lines[($i + 1)];
            }

            if (empty($line) && empty($prev))
            {
                continue;
            }

            if (empty($line) && empty($next))
            {
                continue;
            }

            $keep[] = $line;
        }

        $total = count($keep);
        $lines = array();
        $count = 0;
        for ($i = 0; $i < $total; $i++)
        {
            $line = $keep[$i];
            if (empty($line))
            {
                $count++;
                continue;
            }

            if (!isset($lines[$count]))
            {
                $lines[$count] = array();
            }

            $lines[$count][] = $line;
        }

        $keep = array();
        foreach ($lines as $tokens)
        {
            $keep[] = implode(' ', $tokens);
        }

        $this->text = trim(implode('<br><br>', $keep));

        $this->renderVariables();
        $this->renderImages();
    }

    protected function renderVariables() : void
    {
        $vars = array(
            'appURL' => APP_URL
        );

        foreach ($vars as $var => $value)
        {
            $this->text = str_replace('$' . $var, $value, $this->text);
        }
    }

    /**
     * @return LinkedImage[]
     */
    public function detectImages() : array
    {
        if (!str_contains($this->rawText, '{')) {
            return array();
        }

        $result = array();
        preg_match_all('/{image:[ ]*(.+)}|{image[ ]*([0-9]+)(%|px):[ ]*(.+)}/U', $this->rawText, $result, PREG_PATTERN_ORDER);

        if (empty($result[0][0])) {
            return array();
        }

        $indexes = array_keys($result[0]);
        $results = array();

        foreach ($indexes as $index)
        {
            $match = $result[0][$index];
            $width = null;
            $imageName = null;

            // Image without width specified
            if (!empty($result[1][$index])) {
                $imageName = $result[1][$index];
            } else if (!empty($result[2][$index])) {
                $percent = parseNumber($result[2][$index] . $result[3][$index]);
                if (!$percent->isEmpty()) {
                    $width = $percent;
                }

                $imageName = $result[4][$index];
            }

            if ($imageName === null) {
                continue;
            }

            $results[] = new LinkedImage(
                $imageName,
                $width,
                $match
            );
        }

        return $results;
    }

    protected function renderImages() : void
    {
        foreach ($this->detectImages() as $image) {
            $this->renderImage($image);
        }
    }

    protected function renderImage(LinkedImage $image) : void
    {
        $linkAttr = AttributeCollection::create()
            ->attr('href', $image->getURL())
            ->attr('target', '_blank')
            ->addClass('whatsnew-image');

        $imgAttr = AttributeCollection::create()
            ->setKeepIfEmpty('alt')
            ->attr('src', $image->getURL())
            ->attr('alt', '')
            ->style('width', $image->renderWidth(), false);

        $replace = (string)HTMLTag::create('a', $linkAttr)
            ->setContent(HTMLTag::create('img', $imgAttr));

        $this->text = str_replace($image->getMatchedText(), $replace, $this->text);
    }

    public function getPlainText() : string
    {
        $text = str_replace(array("\n", "\t"), ' ', $this->rawText);

        while (str_contains($text, '  '))
        {
            $text = str_replace('  ', ' ', $text);
        }

        return $text;
    }

    public function getAuthor() : string
    {
        return $this->author;
    }

    public function hasAuthor() : bool
    {
        return !empty($this->author);
    }

    public function getIssue() : string
    {
        return $this->issue;
    }

    public function hasIssue() : bool
    {
        return !empty($this->issue);
    }

    /**
     * @return array{text:string,author:string,issue:string}
     */
    public function toArray() : array
    {
        return array(
            'text' => $this->getWhatsNew()->getMarkdownRenderer()->render($this->getText()),
            'author' => $this->getAuthor(),
            'issue' => $this->getIssue()
        );
    }

    public function setText(string $text) : self
    {
        $this->rawText = $text;
        return $this;
    }

    public function setAuthor(string $author) : self
    {
        if($this->getLanguage()->isDeveloperOnly())
        {
            $this->author = $author;
        }

        return $this;
    }

    public function setIssue(string $issue) : self
    {
        if($this->getLanguage()->isDeveloperOnly())
        {
            $this->issue = $issue;
        }

        return $this;
    }

    public function setCategoryLabel(string $label) : self
    {
        $this->category = $this->getLanguage()->getCategoryByLabel($label);
        return $this;
    }
}
