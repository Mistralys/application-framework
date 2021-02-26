<?php
/**
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Sebastian Mordziol
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * @category HTML
 * @package  HTML_QuickForm2
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Equals extends HTML_QuickForm2_Rule
{
    /**
     * Validates the owner element
     *
     * @return   bool    whether (element_value operator operand) expression is true
     */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        $config = $this->getConfig();

        //echo '<pre>';var_dump($value);echo '<br/>';var_dump($config);echo '</pre>';

        if ($value === $config) {
            return true;
        }

        return false;
    }
}