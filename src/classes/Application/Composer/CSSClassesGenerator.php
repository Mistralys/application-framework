<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

use Application\AppFactory;
use AppUtils\FileHelper\FileInfo;
use ReflectionClass;
use UI\CSSClasses;

/**
 * Generates the clientside JavaScript reference file for the
 * {@see CSSClasses} PHP class constants, so that clientside UI
 * handling can use the same class name definitions.
 *
 * The generated file is written to the theme's JS folder and
 * is automatically included in all page requests as part of
 * the core scripts.
 *
 * @package Application
 * @subpackage Composer
 * @see CSSClasses
 * @see ComposerScripts::generateCSSClassesJS()
 */
class CSSClassesGenerator
{
    public const string TARGET_JS_FILE = 'ui/css-classes.js';

    private string $targetPath;

    public function __construct()
    {
        $this->targetPath = AppFactory::createTheme()->getDefaultJavascriptsPath().'/'.self::TARGET_JS_FILE;
    }

    public function generate() : void
    {
        $content = $this->buildContent();

        FileInfo::factory($this->targetPath)->putContents($content);

        echo sprintf(
            '- Written to: [%s]'.PHP_EOL,
            self::TARGET_JS_FILE
        );
    }

    private function buildContent() : string
    {
        $constants = $this->resolveConstants();
        $lines = array();

        $lines[] = '/**';
        $lines[] = ' * Clientside reference for CSS class name constants.';
        $lines[] = ' * Auto-generated from {@see \UI\CSSClasses} - do not edit manually.';
        $lines[] = ' *';
        $lines[] = ' * @generated '.date('Y-m-d H:i:s');
        $lines[] = ' * @see src/classes/UI/CSSClasses.php';
        $lines[] = ' */';
        $lines[] = 'class CSSClasses {';

        foreach ($constants as $name => $value) {
            $lines[] = sprintf(
                '    static get %s() { return %s; }',
                $name,
                json_encode($value, JSON_THROW_ON_ERROR)
            );
        }

        $lines[] = '}';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array<string,string>
     */
    private function resolveConstants() : array
    {
        $reflection = new ReflectionClass(CSSClasses::class);
        $constants = $reflection->getConstants();

        ksort($constants);

        return $constants;
    }
}



