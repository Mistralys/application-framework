<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

/**
 * Parses a raw keyword string of the form "TERM (context description)"
 * into its constituent parts.
 *
 * Edge cases handled:
 * - No parenthesis present → context is empty string.
 * - Nested parentheses → context is trimmed to the last `)`.
 * - Empty string → returns empty keyword and context.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordParser
{
    /**
     * Parses a raw keyword string into its keyword and context parts.
     *
     * @param  string $rawKeyword Raw keyword string, e.g. "SOCCER (default enrichment system)".
     * @return array{keyword: string, context: string}
     */
    public static function parse(string $rawKeyword) : array
    {
        if($rawKeyword === '')
        {
            return array('keyword' => '', 'context' => '');
        }

        $pos = strpos($rawKeyword, ' (');

        if($pos === false)
        {
            return array('keyword' => $rawKeyword, 'context' => '');
        }

        $keyword = substr($rawKeyword, 0, $pos);
        $context = substr($rawKeyword, $pos + 2); // skip the " (" delimiter

        // Trim the closing ")" — use strrpos to handle nested parentheses greedily.
        $lastParen = strrpos($context, ')');

        if($lastParen !== false)
        {
            $context = substr($context, 0, $lastParen);
        }

        return array('keyword' => $keyword, 'context' => $context);
    }
}
