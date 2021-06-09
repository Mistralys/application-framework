<?php
/**
 * Base class for <input> elements
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
 *
 * @category  HTML
 * @package   HTML_QuickForm2
 * @author    Alexey Borzov <avb@php.net>
 * @author    Bertrand Mansion <golgote@mamasam.com>
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Base class for simple HTML_QuickForm2 elements (not Containers)
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element.php';

/**
 * Base class for <input> elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_HTMLDatePicker extends HTML_QuickForm2_Element
{
    /**
     * 'type' attribute should not be changeable
     * @var array
     */
    protected $watchedAttributes = array('id', 'name', 'type');

    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
    }

    protected function onAttributeChange($name, $value = null)
    {
        if ('type' == $name)
        {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Attribute 'type' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

    public function getType()
    {
        return 'datetime-local';
    }

    public function setValue($value)
    {
        $this->setAttribute('value', (string)$value);
        return $this;
    }

    public function getRawValue()
    {
        return $this->getAttribute('disabled') ? null : $this->getAttribute('value');
    }

    public function __toString()
    {
        return '<input type="date"' . $this->getAttributes(true) . ' />';
    }
}

?>