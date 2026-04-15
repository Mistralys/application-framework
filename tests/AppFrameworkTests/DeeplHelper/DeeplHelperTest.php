<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\DeeplHelper;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application_Driver;
use DeeplHelper;

/**
 * Tests that the DeepL translator is created with the correct
 * regional target language code, avoiding the exception thrown
 * by the Translator library when "EN" or "PT" is used as target.
 *
 * @package Application
 * @subpackage UnitTests
 */
final class DeeplHelperTest extends ApplicationTestCase
{
    private const FAKE_API_KEY = 'test-api-key-12345:fx';

    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();

        // Store a fake API key so createTranslator() does not throw for a missing key.
        // The transaction is rolled back in tearDown(), so this is non-destructive.
        Application_Driver::getInstance()
            ->getSettings()
            ->set(DeeplHelper::APP_SETTING_API_KEY, self::FAKE_API_KEY);
    }

    /**
     * A GB country must produce "EN-GB" as target language, not "EN".
     */
    public function test_targetLanguageGB() : void
    {
        $de = $this->createTestCountry('de');
        $gb = $this->createTestCountry('gb');

        $translator = AppFactory::createDeeplHelper()->createTranslator($de, $gb);

        $this->assertSame('EN-GB', $translator->getTargetLanguage());
    }

    /**
     * A US country must produce "EN-US" as target language, not "EN".
     */
    public function test_targetLanguageUS() : void
    {
        $de = $this->createTestCountry('de');
        $us = $this->createTestCountry('us');

        $translator = AppFactory::createDeeplHelper()->createTranslator($de, $us);

        $this->assertSame('EN-US', $translator->getTargetLanguage());
    }

    /**
     * The source language must still be the plain two-letter uppercased code.
     */
    public function test_sourceLanguageIsNotAffected() : void
    {
        $de = $this->createTestCountry('de');
        $gb = $this->createTestCountry('gb');

        $translator = AppFactory::createDeeplHelper()->createTranslator($de, $gb);

        $this->assertSame('DE', $translator->getSourceLanguage());
    }

    /**
     * A non-deprecated target language (e.g. DE) must pass through unchanged.
     */
    public function test_nonDeprecatedTargetLanguage() : void
    {
        $gb = $this->createTestCountry('gb');
        $de = $this->createTestCountry('de');

        $translator = AppFactory::createDeeplHelper()->createTranslator($gb, $de);

        $this->assertSame('DE', $translator->getTargetLanguage());
    }
}
