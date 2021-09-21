<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
    /**
     * @var string[]
     */
    protected $classes = array();

    public function render() : string
    {
        OutputBuffering::start();

        ?>
            <tr class="prop-merged <?php echo implode(' ', $this->classes) ?>">
                <td colspan="2">
                    <?php echo $this->label ?>
                </td>
            </tr>
        <?php

        return OutputBuffering::get();
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb();
    }
}
