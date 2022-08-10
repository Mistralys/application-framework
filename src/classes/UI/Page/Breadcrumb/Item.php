<?php

declare(strict_types=1);

use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;

class UI_Page_Breadcrumb_Item implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    protected UI_Page_Breadcrumb $breadcrumb;
    protected string $label;
    protected string $mode = '';
    protected string $url = '';
    protected string $javascript = '';
    protected bool $first = false;
    protected bool $last = false;

    /**
     * @param UI_Page_Breadcrumb $breadcrumb
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @throws UI_Exception
     */
    public function __construct(UI_Page_Breadcrumb $breadcrumb, $label)
    {
        $this->label = toString($label);
        $this->breadcrumb = $breadcrumb;
    }

    public function getUI() : UI
    {
        return $this->breadcrumb->getUI();
    }

    /**
     * Makes the item link to the specified URL.
     *
     * @param string|array<string,mixed> $urlOrParams
     * @return $this
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function makeLinked($urlOrParams) : self
    {
        $url = $urlOrParams;
        if (is_array($urlOrParams)) {
            $url = Application_Request::getInstance()->buildURL($urlOrParams);
        }

        $this->mode = 'linked';
        $this->url = $url;

        return $this;
    }

    /**
     * Turns the item into a javascript click link, which will
     * execute the specified javascript code when clicked.
     *
     * @param string|UI_Renderable_Interface $javascript
     * @return $this
     * @throws UI_Exception
     */
    public function makeClickable($javascript) : self
    {
        $this->mode = 'clickable';
        $this->javascript = toString($javascript);

        return $this;
    }

    /**
     * Makes the item link to the specified administration area.
     *
     * @param Application_Admin_Area $area
     * @param array<string,mixed> $params
     * @return $this
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function makeLinkedFromArea(Application_Admin_Area $area, array $params = array()) : self
    {
        return $this->makeLinked($area->getURL($params));
    }

    /**
     * Makes the item link to the specified administration mode.
     *
     * @param Application_Admin_Area_Mode $mode
     * @param array<string,mixed> $params
     * @return $this
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function makeLinkedFromMode(Application_Admin_Area_Mode $mode, array $params = array()) : self
    {
        return $this->makeLinked($mode->getURL($params));
    }

    /**
     * Makes the item link to the specified administration submode.
     *
     * @param Application_Admin_Area_Mode_Submode $submode
     * @param array<string,mixed> $params
     * @return $this
     */
    public function makeLinkedFromSubmode(Application_Admin_Area_Mode_Submode $submode, array $params = array()) : self
    {
        return $this->makeLinked($submode->getURL($params));
    }

    public function render() : string
    {
        if (empty($this->mode)) {
            return $this->getLabel();
        }

        return $this->breadcrumb->getPage()->renderTemplate(
            'breadcrumb.item',
            array(
                'item' => $this
            )
        );
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function getJavascript() : string
    {
        return $this->javascript;
    }

    public function getMode() : string
    {
        return $this->mode;
    }

    public function isLinked() : bool
    {
        return $this->mode === 'linked';
    }

    public function isClickable() : bool
    {
        return $this->mode === 'clickable';
    }

    public function reset() : void
    {
        $this->first = false;
        $this->last = false;
    }

    /**
     * @return $this
     */
    public function setFirst() : self
    {
        $this->first = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function setLast() : self
    {
        $this->last = true;
        return $this;
    }

    public function isFirst() : bool
    {
        return $this->first;
    }

    public function isLast() : bool
    {
        return $this->last;
    }

    /**
     * @param array<string,mixed> $params
     * @return $this
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function makeLinkedRefresh(array $params=array()) : self
    {
        $request = Application_Request::getInstance();
        $request->getRefreshParams($params);
        return $this->makeLinked($params);
    }
}
