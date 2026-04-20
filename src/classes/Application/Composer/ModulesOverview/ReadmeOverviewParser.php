<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

/**
 * Utility class for extracting the `## Overview` section text
 * from a module's README.md file.
 *
 * @package Application
 * @subpackage Composer
 */
final class ReadmeOverviewParser
{
    /**
     * Extracts the text content of the `## Overview` section from a README.md file.
     *
     * Returns the trimmed text between the `## Overview` heading and the next
     * `##` heading (or the end of the file). Returns `null` if the file does
     * not exist or contains no `## Overview` section.
     *
     * @param string $readmePath Absolute path to the README.md file.
     * @return string|null The trimmed overview text, or null if not found.
     */
    public static function extractOverview(string $readmePath): ?string
    {
        if (!file_exists($readmePath)) {
            return null;
        }

        $content = file_get_contents($readmePath);

        if ($content === false) {
            return null;
        }

        if (!preg_match('/^##\s+Overview[^\n]*\n(.*?)(?=^##(?!#)|\z)/ms', $content, $matches)) {
            return null;
        }

        $text = trim($matches[1]);

        if ($text === '') {
            return null;
        }

        return $text;
    }
}
