<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single row in a {@see GlossarySection},
 * holding the cell values for that row.
 *
 * @package Application
 * @subpackage Composer
 */
final class GlossarySectionEntry
{
    /**
     * @var string[]
     */
    private array $values;

    /**
     * @param string[] $values Cell values for this row.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return string[]
     */
    public function getValues() : array
    {
        return $this->values;
    }
}
