<?php
/**
 * Class for Rich Text Editor elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Andreas Martin <actionandy@1und1.de>
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

class HTML_QuickForm2_Element_RichText extends HTML_QuickForm2_Element
{
    protected $persistent = true;

   /**
    * Value for textarea field
    * @var  string
    */
    protected $value = null;

    public function getType()
    {
        return 'richtext';
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getRawValue()
    {
        return empty($this->attributes['disabled'])? $this->value: null;
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {

            $this->addClass('redactor');

            return $this->getIndent() . '<textarea ' . $this->getAttributes(true) .
                   '>' . preg_replace("/(\r\n|\n|\r)/", '&#010;', htmlspecialchars(
                        $this->value, ENT_QUOTES, self::getOption('charset')
                   )) . '</textarea>';
        }
    }

    public function getFrozenHtml()
    {
        $value = htmlspecialchars($this->value, ENT_QUOTES, self::getOption('charset'));
        if ('off' == $this->getAttribute('wrap')) {
            $html = $this->getIndent() . '<pre>' . $value .
                    '</pre>' . self::getOption('linebreak');
        } else {
            $html = nl2br($value) . self::getOption('linebreak');
        }
        return $html . $this->getPersistentContent();
    }
}
?>
