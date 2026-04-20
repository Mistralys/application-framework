<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

/**
 * Renders JS icon accessor methods for insertion between the
 * `START METHODS` / `END METHODS` markers in a JS icon object.
 *
 * Each generated method follows the pattern:
 * <pre>
 *     {MethodName}:function() { return this.SetType('{iconName}'); },
 * </pre>
 *
 * The method name is the icon ID converted to PascalCase
 * (underscores are used as word separators).
 *
 * @package Application
 * @subpackage Composer
 * @see AbstractLanguageRenderer
 * @see PHPRenderer
 */
class JSRenderer extends AbstractLanguageRenderer
{
    /**
     * Renders a single JS icon accessor method.
     *
     * @param IconDefinition $icon
     * @return string
     */
    protected function renderMethod(IconDefinition $icon) : string
    {
        $methodName = $this->toPascalCase($icon->getID());
        $setTypeArgs = $this->renderSetTypeArgs($icon);

        return '    ' . $methodName . ':function() { return this.SetType(' . $setTypeArgs . '); },' . PHP_EOL;
    }

}
