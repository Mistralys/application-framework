<?php

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
    protected $classes = array();

    public function render()
    {
        ob_start();
        ?>
            <tr class="prop-merged <?php echo implode(' ', $this->classes) ?>">
                <td colspan="2">
                    <?php echo $this->label ?>
                </td>
            </tr>
        <?php
        return ob_get_clean();
    }
    
    protected function filterValue($text) : UI_StringBuilder
    {
        return sb();
    }
}
