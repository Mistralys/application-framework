<?php

declare(strict_types=1);

namespace UI\Form\Element\VisualSelect;

use HTML_QuickForm2_Element_VisualSelect;
use HTML_QuickForm2_Element_VisualSelect_Optgroup;
use UI_Renderable_Interface;

class ImageSet
{
    public const string ATTRIBUTE_SET_ID = 'data-image-set';
    public const string PROPERTY_IMAGE_SET = 'image-set';

    private string $id;
    private string $label;
    private HTML_QuickForm2_Element_VisualSelect $select;

    public function __construct(HTML_QuickForm2_Element_VisualSelect $select, string $id, string $label)
    {
        $this->select = $select;
        $this->id = $id;
        $this->label = $label;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function addGroup(string $label) : HTML_QuickForm2_Element_VisualSelect_Optgroup
    {
        $group = $this->select->addOptgroup($label);
        $group->setAttribute(self::ATTRIBUTE_SET_ID, $this->getID());
        $group->setRuntimeProperty(self::PROPERTY_IMAGE_SET, $this);
        return $group;
    }

    /**
     * Adds an image to select: simultaneously adds it to the
     * select element and the list of images.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @param string $value
     * @param string $url
     * @return $this
     */
    public function addImage($label, string $value, string $url) : self
    {
        $this->select->addImage(
            $label,
            $value,
            $url,
            array(
                self::ATTRIBUTE_SET_ID => $this->getID()
            )
        );

        return $this;
    }
}
