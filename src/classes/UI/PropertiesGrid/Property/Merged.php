<?php

declare(strict_types=1);

use AppUtils\OutputBuffering;

class UI_PropertiesGrid_Property_Merged extends UI_PropertiesGrid_Property
{
    /**
     * @var string[]
     */
    private array $classes = array();

    public function render() : string
    {
        OutputBuffering::start();

        ?>
            <tr class="prop-merged <?php echo implode(' ', $this->classes) ?>">
                <td colspan="2">
                    <?php echo $this->resolveText() ?>
                </td>
            </tr>
        <?php

        return OutputBuffering::get();
    }

    public function addClass(string $class) : self
    {
        if(!in_array($class, $this->classes, true)) {
            $this->classes[] = $class;
        }

        return $this;
    }
    
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb()->add($value);
    }
}
