<?php
/**
 * @package User Interface
 * @subpackage Form Elements
 */

declare(strict_types=1);

use AppLocalize\Localization;
use AppUtils\ConvertHelper\JSONConverter;

/**
 * Bootstrap-based datepicker element for selecting dates.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author   Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see http://eternicode.github.io/bootstrap-datepicker
 * @see https://github.com/eternicode/bootstrap-datepicker
 */
class HTML_QuickForm2_Element_Datepicker extends HTML_QuickForm2_Element_InputText
{
    protected array $clientOptions = array(
        'format' => 'dd/mm/yy',
        'todayBtn' => 'linked',
        'clearBtn' => true,
    );
    
    public function __toString()
    {
        if($this->frozen) {
            return parent::__toString();
        }
        
        $id = $this->getId();
        if(empty($id)) {
            $id = nextJSID();
            $this->setId($id);
        }
        
        $this->setAttribute('placeholder', $this->getPlaceholder());
        
        $ui = UI::getInstance();
        $ui->addJavascript('bootstrap-datepicker.min.js');
        $ui->addStylesheet('bootstrap-datepicker.min.css');
        
        $this->clientOptions['language'] = Localization::getAppLocale()->getLanguageCode();
        
        $ui->addJavascriptOnload(sprintf(
            "$('#%s').datepicker(%s)",
            $id,
            JSONConverter::var2json($this->clientOptions)
        ));

        return
        '<div class="input-append input-date">'.
            parent::__toString().
            '<span class="add-on" onclick="$(\'#'.$id.'\').focus()">'.
                UI::icon()->calendar().
            '</span>'.
        '</div>';
    }
    
    public function getPlaceholder()
    {
        $placeholder = $this->clientOptions['format'];
        
        switch(Localization::getAppLocale()->getLanguageCode()) {
            case 'de':
                $replaces = array(
                    'd' => 't',
                    'm' => 'm',
                    'y' => 'j'
                );
                $placeholder = str_replace(array_keys($replaces), array_values($replaces), $placeholder);
                break;
        }
        
        return $placeholder;
    }
    
    public const string REGEX_DATE = '%\A[0-9]{2}/[0-9]{2}/[0-9]{2}\z%m';
    
    public function getRegex() : string
    {
        return self::REGEX_DATE;
    }
    
   /**
    * Retrieves a date object for a value of the element,
    * or null otherwise (if the value is empty, for ex.).
    * 
    * @param string|NULL $value
    * @return null|DateTime
    */
    public function getDate(?string $value) : ?DateTime
    {
        if(empty($value)) {
            return null;
        }
        
        $tokens = explode('/', $value);

        return new DateTime(sprintf('20%s-%s-%s', $tokens[2], $tokens[1], $tokens[0]));
    }
}