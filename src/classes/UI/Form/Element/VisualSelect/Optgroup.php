<?php
/**
 * File containing the class {@see HTML_QuickForm2_Element_VisualSelect_Optgroup}.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_VisualSelect_Optgroup
 */

declare(strict_types=1);

use UI\Form\Element\VisualSelect\ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption;

/**
 * Custom option group that adds methods specific to the
 * visual selection element. Use the {@see HTML_QuickForm2_Element_VisualSelect_Optgroup::addImage()}
 * method to add images, instead of the regular `addOption()`
 * method.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method VisualSelectOption addOption($text, $value, $attributes = null)
 */
class HTML_QuickForm2_Element_VisualSelect_Optgroup extends HTML_QuickForm2_Element_Select_Optgroup
{
    public function __construct(&$values, &$possibleValues, $label, $attributes = null)
    {
        parent::__construct($values, $possibleValues, $label, $attributes);

        $this->setOptionClass(VisualSelectOption::class);
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $value
     * @param string $url
     * @return VisualSelectOption
     * @throws HTML_QuickForm2_InvalidArgumentException
     * @throws UI_Exception
     */
    public function addImage($label, string $value, string $url) : VisualSelectOption
    {
        return $this->addOption(
            toString($label),
            $value,
            $this->resolveImageAttributes($url)
        );
    }

    /**
     * @param string $url
     * @return array<string,string>
     */
    private function resolveImageAttributes(string $url) : array
    {
        $attributes = array('image-url' => $url);

        $setID = $this->getImageSetID();
        if(!empty($setID)) {
            $attributes[ImageSet::ATTRIBUTE_SET_ID] = $setID;
        }

        return $attributes;
    }

    public function getImageSetID() : string
    {
        return (string)$this->getAttribute(ImageSet::ATTRIBUTE_SET_ID);
    }

    private ?string $elementID = null;

    public function getElementID() : string
    {
        if(!isset($this->elementID)) {
            $this->elementID = nextJSID();
        }

        return $this->elementID;
    }
}
