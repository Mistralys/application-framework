<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion\CategoryItem}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion\CategoryItem
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion;

use Application\WhatsNew;
use Application_Driver;
use AppUtils\ConvertHelper;
use SimpleXMLElement;
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

    protected function renderImages() : void
    {
        if (strpos($this->text, '{') === false)
        {
            return;
        }

        $result = array();
        preg_match_all('/{image:[ ]*(.+)}/U', $this->text, $result, PREG_PATTERN_ORDER);

        if (!isset($result[1]) && isset($result[1][0]))
        {
            return;
        }

        $theme = Application_Driver::getInstance()->getTheme();

        foreach ($result[1] as $idx => $imageName)
        {
            $search = $result[0][$idx];
            $imgURL = $theme->getImageURL('whatsnew/' . $imageName);
            $replace = '<a href="' . $imgURL . '" target="_blank" class="whatsnew-image"><img src="' . $imgURL . '" alt=""/></a>';
            $this->text = str_replace($search, $replace, $this->text);
        }
    }

    public function getPlainText() : string
    {
        $text = str_replace(array("\n", "\t"), ' ', $this->rawText);
        while (strpos($text, '  ') !== false)
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
            'text' => $this->getWhatsNew()->getParseDown()->parse($this->getText()),
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
