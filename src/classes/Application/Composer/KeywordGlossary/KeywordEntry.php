<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

/**
 * Immutable value object representing a single keyword entry with its
 * optional context and the list of module IDs that declare it.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordEntry
{
    /**
     * @var string
     */
    private string $keyword;

    /**
     * @var string
     */
    private string $context;

    /**
     * @var string[]
     */
    private array $moduleIds;

    /**
     * @param string   $keyword   The keyword term (e.g. "SOCCER").
     * @param string   $context   Optional context description (e.g. "default enrichment system").
     * @param string[] $moduleIds List of module IDs that declare this keyword.
     */
    public function __construct(string $keyword, string $context, array $moduleIds)
    {
        $this->keyword   = $keyword;
        $this->context   = $context;
        $this->moduleIds = $moduleIds;
    }

    public function getKeyword() : string
    {
        return $this->keyword;
    }

    public function getContext() : string
    {
        return $this->context;
    }

    /**
     * @return string[]
     */
    public function getModuleIds() : array
    {
        return $this->moduleIds;
    }

    /**
     * Returns a new instance with the given module ID appended (immutable update).
     *
     * @param string $moduleId
     * @return self
     */
    public function addModuleId(string $moduleId) : self
    {
        $ids   = $this->moduleIds;
        $ids[] = $moduleId;

        return new self($this->keyword, $this->context, $ids);
    }
}
