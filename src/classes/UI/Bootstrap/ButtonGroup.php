<?php

declare(strict_types=1);

use Application\AppFactory;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface;
use UI\Interfaces\ButtonSizeInterface;
use UI\Traits\ButtonSizeTrait;

class UI_Bootstrap_ButtonGroup
    extends UI_Bootstrap
    implements ButtonSizeInterface
{
    use ButtonSizeTrait;

    public const int ERROR_BUTTON_NAME_NOT_FOUND = 159301;
    public const int ERROR_BUTTON_NAME_NOT_SET = 159302;

    /**
    * @var ButtonGroupItemInterface[]
    */
    protected array $buttons = array();

   /**
    * Adds a button to the group.
    *
    * @param ButtonGroupItemInterface $button
    * @param string|null $name Optional name to be able to get/select the button later.
    * @return $this
    */
    public function addButton(ButtonGroupItemInterface $button, ?string $name=null) : self
    {
        $this->buttons[] = $button;

        if(!empty($name)) {
            $button->setName($name);
        }

        return $this;
    }

    /**
     * @param ButtonGroupItemInterface[] $buttons
     * @return self
     */
    public function addButtons(array $buttons) : self
    {
        foreach($buttons as $button) {
            $this->addButton($button);
        }

        return $this;
    }

    protected function _render() : string
    {
        if(empty($this->buttons)) {
            return '';
        }

        $this->addClass('btn-group');

        $html =
        '<div'.$this->renderAttributes().'>';
            foreach($this->buttons as $button)
            {
                if(!empty($this->buttonSize)) {
                    $button->makeSize($this->buttonSize);
                }

                $html .= $button->render();
            }
            $html .=
        '</div>';

        return $html;
    }

    public function nameExists(string $name) : bool
    {
        foreach($this->getAll() as $button) {
            if($button->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @return ButtonGroupItemInterface
     * @throws UI_Exception
     */
    public function getByName(string $name) : ButtonGroupItemInterface
    {
        foreach($this->getAll() as $button) {
            if($button->getName() === $name) {
                return $button;
            }
        }

        throw new UI_Exception(
            'Button not found.',
            sprintf('Button with name "%s" not found in button group.', $name),
            self::ERROR_BUTTON_NAME_NOT_FOUND
        );
    }

    /**
     * @return ButtonGroupItemInterface[]
     */
    public function getAll() : array
    {
        return $this->buttons;
    }

    public function selectButton(UI_Button $button) : self
    {
        if(empty($button->getName())) {
            throw new UI_Exception(
                'Button name not set.',
                'Button name must be set to be able to select it.',
                self::ERROR_BUTTON_NAME_NOT_SET
            );
        }

        $this->selectByName($button->getName());

        return $this;
    }

    public function selectByName(string $name) : self
    {
        $this->getByName($name)->makeActive();
        return $this;
    }

    public function selectByRequestParam(string $paramName) : self
    {
        $value = (string)AppFactory::createRequest()->getParam($paramName);

        // Deactivate all buttons even if the target button is not found
        foreach($this->getAll() as $button) {
            $button->makeActive(false);
        }

        if(!empty($value) && $this->nameExists($value)) {
            $this->selectByName($value);
        }

        return $this;
    }

    public function getSelected() : ?ButtonGroupItemInterface
    {
        foreach($this->getAll() as $button) {
            if($button->isActive()) {
                return $button;
            }
        }

        return null;
    }
}
