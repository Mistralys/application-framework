<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer;

use Application\Composer\BuildMessages;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see BuildMessages}.
 */
final class BuildMessagesTest extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();
        BuildMessages::reset();
    }

    protected function tearDown() : void
    {
        BuildMessages::reset();
        parent::tearDown();
    }

    public function test_hasMessages_falseWhenEmpty() : void
    {
        $this->assertFalse(BuildMessages::hasMessages());
    }

    public function test_addMessage_hasMessagesReturnsTrue() : void
    {
        BuildMessages::addMessage('TestSource', BuildMessages::LEVEL_NOTICE, 'Test message.');

        $this->assertTrue(BuildMessages::hasMessages());
    }

    public function test_hasErrors_falseWithOnlyWarnings() : void
    {
        BuildMessages::addMessage('TestSource', BuildMessages::LEVEL_WARNING, 'Warning only.');

        $this->assertFalse(BuildMessages::hasErrors());
    }

    public function test_hasErrors_trueWhenErrorPresent() : void
    {
        BuildMessages::addMessage('TestSource', BuildMessages::LEVEL_ERROR, 'An error.');

        $this->assertTrue(BuildMessages::hasErrors());
    }

    public function test_addMessage_duplicateSuppressed() : void
    {
        BuildMessages::addMessage('SrcA', BuildMessages::LEVEL_WARNING, 'Repeated msg.');
        BuildMessages::addMessage('SrcA', BuildMessages::LEVEL_WARNING, 'Repeated msg.');
        BuildMessages::addMessage('SrcB', BuildMessages::LEVEL_NOTICE, 'Different msg.');

        // Save and reload to count entries
        $filePath = sys_get_temp_dir() . '/bm-dedup-test-' . getmypid() . '.json';
        BuildMessages::saveToFile($filePath);
        $decoded = json_decode((string)file_get_contents($filePath), true);
        unlink($filePath);

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded, 'Duplicate entry must be suppressed — only 2 unique messages expected.');
    }

    public function test_reset_clearsMessages() : void
    {
        BuildMessages::addMessage('TestSource', BuildMessages::LEVEL_NOTICE, 'Msg.');
        BuildMessages::reset();

        $this->assertFalse(BuildMessages::hasMessages());
    }

    public function test_addWarning_convenience() : void
    {
        BuildMessages::addWarning('TestSource', 'A warning.');

        $this->assertTrue(BuildMessages::hasMessages());
        $this->assertFalse(BuildMessages::hasErrors());
    }

    public function test_addError_convenience() : void
    {
        BuildMessages::addError('TestSource', 'An error msg.');

        $this->assertTrue(BuildMessages::hasErrors());
    }

    public function test_saveToFile_andLoadFromFile_roundTrip() : void
    {
        $filePath = sys_get_temp_dir() . '/build-messages-test-' . getmypid() . '.json';

        BuildMessages::addMessage('SrcA', BuildMessages::LEVEL_NOTICE, 'Notice one.');
        BuildMessages::addMessage('SrcA', BuildMessages::LEVEL_WARNING, 'Warning two.');
        BuildMessages::saveToFile($filePath);

        BuildMessages::reset();
        $this->assertFalse(BuildMessages::hasMessages(), 'Messages must be empty after reset.');

        BuildMessages::loadFromFile($filePath);
        $this->assertTrue(BuildMessages::hasMessages(), 'Messages must be restored after loadFromFile.');
        $this->assertFalse(BuildMessages::hasErrors(), 'No errors were saved — hasErrors must be false.');

        unlink($filePath);
    }

    public function test_clearFile_removesFile() : void
    {
        $filePath = sys_get_temp_dir() . '/build-messages-clear-test-' . getmypid() . '.json';
        file_put_contents($filePath, '[]');
        $this->assertFileExists($filePath);

        BuildMessages::clearFile($filePath);

        $this->assertFileDoesNotExist($filePath);
    }

    public function test_clearFile_doesNotFailForMissingFile() : void
    {
        // Must not throw or error — simply returns without doing anything
        BuildMessages::clearFile('/nonexistent/path/to/messages.json');
        $this->addToAssertionCount(1);
    }

    public function test_loadFromFile_silentForMissingFile() : void
    {
        BuildMessages::loadFromFile('/nonexistent/path/to/messages.json');

        $this->assertFalse(BuildMessages::hasMessages());
    }

    public function test_printSummary_doesNothingWhenEmpty() : void
    {
        ob_start();
        BuildMessages::printSummary();
        $output = (string)ob_get_clean();

        $this->assertSame('', $output);
    }

    public function test_printSummary_outputsWhenMessagesPresent() : void
    {
        BuildMessages::addMessage('TestSrc', BuildMessages::LEVEL_WARNING, 'Test warning.');

        ob_start();
        BuildMessages::printSummary();
        $output = (string)ob_get_clean();

        $this->assertStringContainsString('BUILD MESSAGES', $output);
        $this->assertStringContainsString('TestSrc', $output);
        $this->assertStringContainsString('Test warning.', $output);
    }
}
