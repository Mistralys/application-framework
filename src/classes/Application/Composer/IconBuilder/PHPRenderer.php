<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

/**
 * Renders PHP icon accessor methods for insertion between the
 * `START METHODS` / `END METHODS` markers in a PHP icon class.
 *
 * Each generated method follows the pattern:
 * <pre>
 *     /**
 *      * @return $this
 *      *\/
 *     public function {methodName}() : self { return $this->setType('{iconName}'); }
 * </pre>
 *
 * The method name is the icon ID converted to camelCase
 * (underscores are used as word separators).
 *
 * @package Application
 * @subpackage Composer
 * @see AbstractLanguageRenderer
 * @see JSRenderer
 */
class PHPRenderer extends AbstractLanguageRenderer
{
    /**
     * Renders a single PHP icon accessor method with its docblock.
     *
     * @param IconDefinition $icon
     * @return string
     */
    protected function renderMethod(IconDefinition $icon) : string
    {
        $methodName = lcfirst($this->toPascalCase($icon->getID()));
        $setTypeArgs = $this->renderSetTypeArgs($icon);

        return
            '    /**' . PHP_EOL .
            '     * @return $this' . PHP_EOL .
            '     */' . PHP_EOL .
            '    public function ' . $methodName . '() : self { return $this->setType(' . $setTypeArgs . '); }' . PHP_EOL;
    }

}
