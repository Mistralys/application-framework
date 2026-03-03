<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\KeywordGlossary;

use Application\Composer\KeywordGlossary\KeywordParser;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see KeywordParser}.
 * Pure unit test — no DB, no filesystem dependency.
 */
final class KeywordParserTest extends ApplicationTestCase
{
    /**
     * Keyword with context in parentheses.
     * Input:  'SOCCER (default enrichment system)'
     * Result: keyword='SOCCER', context='default enrichment system'
     */
    public function test_parse_keywordWithContext() : void
    {
        $result = KeywordParser::parse('SOCCER (default enrichment system)');

        $this->assertSame('SOCCER', $result['keyword']);
        $this->assertSame('default enrichment system', $result['context']);
    }

    /**
     * Keyword without parentheses — context must be empty string.
     * Input:  'CoMa'
     * Result: keyword='CoMa', context=''
     */
    public function test_parse_keywordWithoutContext() : void
    {
        $result = KeywordParser::parse('CoMa');

        $this->assertSame('CoMa', $result['keyword']);
        $this->assertSame('', $result['context']);
    }

    /**
     * Keyword with nested parentheses — greedy match to the last ')'.
     * Input:  'Rygnarôk (codename — see Mail Forge (MF))'
     * Result: keyword='Rygnarôk', context='codename — see Mail Forge (MF)'
     */
    public function test_parse_keywordWithNestedParentheses() : void
    {
        $result = KeywordParser::parse('Rygnarôk (codename — see Mail Forge (MF))');

        $this->assertSame('Rygnarôk', $result['keyword']);
        $this->assertSame('codename — see Mail Forge (MF)', $result['context']);
    }

    /**
     * Empty string input — both keyword and context must be empty strings.
     */
    public function test_parse_emptyString() : void
    {
        $result = KeywordParser::parse('');

        $this->assertSame('', $result['keyword']);
        $this->assertSame('', $result['context']);
    }

    /**
     * Keyword with empty parentheses — context must be empty string.
     * Input:  'Term ()'
     * Result: keyword='Term', context=''
     */
    public function test_parse_emptyParentheses() : void
    {
        $result = KeywordParser::parse('Term ()');

        $this->assertSame('Term', $result['keyword']);
        $this->assertSame('', $result['context']);
    }
}
