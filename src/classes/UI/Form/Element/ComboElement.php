<?php
/**
 * @package User Interface
 * @subpackage Form Elements
 */

declare(strict_types=1);

namespace UI\Form\Element;

use HTML_QuickForm2_Element_InputText;
use UI\Form\CustomElementInterface;
use UI\Form\CustomElementTrait;

/**
 * Combo element with a typeahead input field to select
 * elements, as well as add new ones (if enabled).
 *
 * @package User Interface
 * @subpackage Form Elements
 */
class ComboElement extends HTML_QuickForm2_Element_InputText implements CustomElementInterface
{
    use CustomElementTrait;

    public const ELEMENT_TYPE_ID = 'combo';

    public const JS_FILE = 'forms/form/element/combo.js';
    public const CSS_FILE = 'forms/combo.css';

    public static function getElementTypeID(): string
    {
        return self::ELEMENT_TYPE_ID;
    }

    public static function getElementTypeLabel(): string
    {
        return t('Combo input');
    }

    private const HTML_SCAFFOLD = <<<'HTML'
<div class="combo-selected-elements"></div>
%1$s
<div class="combo-search-results"></div>
HTML;


    public function __toString()
    {
        $this->injectJS();

        return sprintf(
            self::HTML_SCAFFOLD,
            parent::__toString()
        );
    }

    private ?string $jsVarName = null;

    public function getJSVarName() : string
    {
        if(!isset($this->jsVarName)) {
            $this->jsVarName = 'combo' . nextJSID();
        }

        return $this->jsVarName;
    }

    private function injectJS() : void
    {
        $ui = $this->getUI();
        $ui->addJavascript(self::JS_FILE);
        $ui->addStylesheet(self::CSS_FILE);

        $ui->addJavascriptHead(sprintf(
            "const %s = new ComboElement('%s')",
            $this->getJSVarName(),
            $this->getId()
        ));

        $ui->addJavascriptOnload(sprintf(
            "%s.start()",
            $this->getJSVarName()
        ));

    }
}
