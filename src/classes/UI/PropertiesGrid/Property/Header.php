<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

class UI_PropertiesGrid_Property_Header extends UI_PropertiesGrid_Property
{
    public function render() : string
    {
        OutputBuffering::start();

        ?>
        <tr class="prop-header">
            <th colspan="2">
                <?php echo $this->label ?>
            </th>
        </tr>
        <?php

        return OutputBuffering::get();
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb();
    }
}
