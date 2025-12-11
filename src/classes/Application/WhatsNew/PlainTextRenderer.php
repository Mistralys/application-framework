<?php

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\WhatsNew\AppVersion\CategoryItem;
use Application\WhatsNew\AppVersion\LanguageCategory;

/**
 * Renders a what's new versions list to a plain text
 * version (for a specific target language).
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PlainTextRenderer
{
    private WhatsNew $whatsNew;

    /**
     * @var string[]
     */
    private array $lines = array();
    private string $separator;

    public function __construct(WhatsNew $whatsNew)
    {
        $this->whatsNew = $whatsNew;
        $this->separator = str_repeat('-', 65);
    }

    public function render(string $langID) : string
    {
        $this->lines = array();

        $versions = $this->whatsNew->getVersionsByLanguage($langID);

        foreach($versions as $version)
        {
            $this->renderVersion($version, $langID);
        }

        return implode(PHP_EOL, $this->lines);
    }

    private function renderVersion(AppVersion $version, string $langID) : void
    {
        $lang = $version->getLanguage($langID);

        $this->addSeparator();
        $this->addLine('V'.$version->getNumber());
        $this->addSeparator();

        $this->addNewline();

        $categories = $lang->getCategories();

        foreach($categories as $category)
        {
            $this->renderCategory($category);
        }

        $this->addNewline();
    }

    private function renderCategory(LanguageCategory $category) : void
    {
        $entries = $category->getItems();

        foreach($entries as $entry)
        {
            $this->renderEntry($entry, $category);
        }
    }

    private function addSeparator() : void
    {
        $this->addLine($this->separator);
    }

    private function addNewline() : void
    {
        $this->addLine('');
    }

    private function addLine(string $line) : void
    {
        $this->lines[] = $line;
    }

    private function renderEntry(CategoryItem $entry, LanguageCategory $category) : void
    {
        $line = sb()
            ->add('-')
            ->add($category->getLabel());

        if($entry->hasIssue())
        {
            $line
                ->add('-')
                ->add($entry->getIssue());
        }

        $line
            ->add(':')
            ->add($entry->getPlainText());

        if($entry->hasAuthor()) {
            $line->parentheses($entry->getAuthor());
        }

        $this->addLine((string)$line);
    }
}
