<?php
/**
 * @package Application
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\Traits;

use Application\Driver\DriverException;
use Application\Interfaces\HiddenVariablesInterface;
use Application_Driver;
use AppUtils\HTMLTag;
use AppUtils\Interfaces\StringableInterface;
use UI_Exception;

/**
 * Trait for managing hidden variables in a UI element.
 *
 * @package Application
 * @subpackage Traits
 * @see HiddenVariablesInterface
 */
trait HiddenVariablesTrait
{
    /**
     * @var array<string,array{value:string,id:string|NULL,public:bool}>
     */
    protected array $hiddenVars = array();

    /**
     * @param array<string,string|number|StringableInterface|NULL> $vars
     * @return $this
     */
    public function addHiddenVars(array $vars) : self
    {
        foreach($vars as $name => $value) {
            $this->addHiddenVar($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string|number|StringableInterface|NULL $value
     * @param string|NULL $id Optional ID attribute for the input element.
     * @param bool $public Public variables are returned by {@see self::getHiddenVars()}.
     *                     Private ones are included in {@see self::renderHiddenInputs()},
     *                     but are not returned by the public methods.
     * @return $this
     * @throws UI_Exception
     */
    public function addHiddenVar(string $name, $value, ?string $id=null, bool $public=true) : self
    {
        $this->hiddenVars[$name] = array(
            'value' => toString($value),
            'id' => $id,
            'public' => $public
        );

        return $this;
    }

    /**
     * @param string $name
     * @param string|number|StringableInterface|NULL $value
     * @param string|null $id
     * @return $this
     * @throws UI_Exception
     */
    protected function addPrivateHiddenVar(string $name, $value, ?string $id=null) : self
    {
        return $this->addHiddenVar($name, $value, $id, false);
    }

    /**
     * @return array<string,string>
     */
    public function getHiddenVars() : array
    {
        $result = array();
        foreach($this->hiddenVars as $name => $data)
        {
            if($data['public'] === false) {
                continue;
            }

            $result[$name] = $data['value'];
        }

        return $result;
    }

    /**
     * Adds all page-related variables (page / mode / submode...) for
     * the current admin screen.
     *
     * NOTE: Only possible when in admin mode.
     *
     * @return $this
     * @throws DriverException
     */
    public function addHiddenScreenVars() : self
    {
        return $this->addHiddenVars(Application_Driver::getInstance()
            ->getActiveScreen()
            ->getPageParams()
        );
    }

    /**
     * @param string[] $classes
     * @return string
     */
    public function renderHiddenInputs(array $classes=array()) : string
    {
        // NOTE: The div can be used clientside, so it must
        // be present even if there are no hidden variables.

        $html = '';

        foreach ($this->hiddenVars as $name => $data)
        {
            $html .= PHP_EOL.'    '.HTMLTag::create('input')
                ->attr('type', 'hidden')
                ->name($name)
                ->attr('value', $data['value'])
                ->id((string)$data['id']);
        }

        return HTMLTag::create('div')
            ->addClass('hiddens')
            ->addClasses($classes)
            ->setContent($html)
            ->render().PHP_EOL;
    }
}
