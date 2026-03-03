<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single section in the keyword
 * glossary, with a heading, column headers, and a list of entries.
 *
 * @package Application
 * @subpackage Composer
 */
final class GlossarySection
{
    /**
     * @var string
     */
    private string $heading;

    /**
     * @var string[]
     */
    private array $columnHeaders;

    /**
     * @var GlossarySectionEntry[]
     */
    private array $entries;

    /**
     * @param string                 $heading       Section heading text.
     * @param string[]               $columnHeaders Column header labels.
     * @param GlossarySectionEntry[] $entries       Rows in this section.
     */
    public function __construct(string $heading, array $columnHeaders, array $entries)
    {
        $this->heading       = $heading;
        $this->columnHeaders = $columnHeaders;
        $this->entries       = $entries;
    }

    public function getHeading() : string
    {
        return $this->heading;
    }

    /**
     * @return string[]
     */
    public function getColumnHeaders() : array
    {
        return $this->columnHeaders;
    }

    /**
     * @return GlossarySectionEntry[]
     */
    public function getEntries() : array
    {
        return $this->entries;
    }
}
