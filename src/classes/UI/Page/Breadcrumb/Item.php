<?php

class UI_Page_Breadcrumb_Item
{
    /**
     * @var UI_Page_Breadcrumb
     */
    protected $breadcrumb;

    protected $label;

    protected $mode;

    protected $url;

    protected $javascript;

    public function __construct(UI_Page_Breadcrumb $breadcrumb, $label)
    {
        $this->label = $label;
        $this->breadcrumb = $breadcrumb;
    }

    /**
     * Makes the item link to the specified URL.
     *
     * @param string|array $urlOrParams
     * @return UI_Page_Breadcrumb_Item
     */
    public function makeLinked($urlOrParams)
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
     * @param string $javascript
     * @return UI_Page_Breadcrumb_Item
     */
    public function makeClickable($javascript)
    {
        $this->mode = 'clickable';
        $this->javascript = $javascript;

        return $this;
    }

    /**
     * Makes the item link to the specified administration area.
     *
     * @param Application_Admin_Area $area
     * @param array $params
     * @return UI_Page_Breadcrumb_Item
     */
    public function makeLinkedFromArea(Application_Admin_Area $area, $params = array())
    {
        return $this->makeLinked($area->getURL($params));
    }

    /**
     * Makes the item link to the specified administration mode.
     *
     * @param Application_Admin_Area_Mode $mode
     * @param array $params
     * @return UI_Page_Breadcrumb_Item
     */
    public function makeLinkedFromMode(Application_Admin_Area_Mode $mode, $params = array())
    {
        return $this->makeLinked($mode->getURL($params));
    }

    /**
     * Makes the item link to the specified administration submode.
     *
     * @param Application_Admin_Area_Mode_Submode $submode
     * @param array $params
     * @return UI_Page_Breadcrumb_Item
     */
    public function makeLinkedFromSubmode(Application_Admin_Area_Mode_Submode $submode, $params = array())
    {
        return $this->makeLinked($submode->getURL($params));
    }

    public function render()
    {
        if (!isset($this->mode)) {
            return $this->label;
        }

        return $this->breadcrumb->getPage()->renderTemplate(
            'breadcrumb.item',
            array(
                'item' => $this
            )
        );
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function isLinked()
    {
        if ($this->mode == 'linked') {
            return true;
        }

        return false;
    }

    public function isClickable()
    {
        if ($this->mode == 'clickable') {
            return true;
        }

        return false;
    }

    protected $first = false;

    protected $last = false;

    public function reset()
    {
        $this->first = false;
        $this->last = false;
    }

    public function setFirst()
    {
        $this->first = true;
    }

    public function setLast()
    {
        $this->last = true;
    }

    public function isFirst()
    {
        return $this->first;
    }

    public function isLast()
    {
        return $this->last;
    }
    
    public function makeLinkedRefresh($params=array())
    {
        $request = Application_Request::getInstance();
        $request->getRefreshParams($params);
        return $this->makeLinked($params);
    }
}