<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer;

use Application\Composer\ComposerScripts;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\FileHelper;
use DateTime;
use ReflectionMethod;

/**
 * Unit tests for {@see ComposerScripts::doUpdateContextGenerateDate()}.
 */
final class ContextGenerateDateTest extends ApplicationTestCase
{
    private ?string $tempFolder = null;

    protected function tearDown() : void
    {
        if($this->tempFolder !== null && is_dir($this->tempFolder)) {
            FileHelper::deleteTree($this->tempFolder);
        }

        $this->tempFolder = null;

        parent::tearDown();
    }

    public function test_doUpdateContextGenerateDateIsPublicStatic() : void
    {
        $method = new ReflectionMethod(ComposerScripts::class, 'doUpdateContextGenerateDate');

        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    public function test_writesParseableTimestampIntoFolder() : void
    {
        $this->tempFolder = sys_get_temp_dir().'/ctx-generate-date-test-'.getmypid().'-'.uniqid('', false);

        $this->assertDirectoryDoesNotExist($this->tempFolder);

        ob_start();
        ComposerScripts::doUpdateContextGenerateDate($this->tempFolder);
        ob_end_clean();

        $file = $this->tempFolder.'/generated-at.txt';

        $this->assertFileExists($file);

        $content = file_get_contents($file);

        $this->assertIsString($content);
        $this->assertNotSame('', trim($content));

        $date = new DateTime(trim($content));

        $this->assertLessThan(300, abs(time() - $date->getTimestamp()));
    }
}
