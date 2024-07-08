<?php

declare(strict_types=1);

namespace UI\Traits;

use UI;
use UI\Interfaces\ButtonSizeInterface;
use UI_Exception;

/**
 * @see ButtonSizeInterface
 */
trait ButtonSizeTrait
{
    protected ?string $buttonSize = null;

    /**
     * @return $this
     */
    public function makeMini() : self
    {
        return $this->makeSize(ButtonSizeInterface::SIZE_MINI);
    }

    /**
     * @return $this
     */
    public function makeSmall() : self
    {
        return $this->makeSize(ButtonSizeInterface::SIZE_SMALL);
    }

    /**
     * @return $this
     */
    public function makeLarge() : self
    {
        return $this->makeSize(ButtonSizeInterface::SIZE_LARGE);
    }

    /**
     * @param string $size
     * @return $this
     * @throws UI_Exception
     */
    public function makeSize(string $size) : self
    {
        $this->requireValidSize($size);

        $this->buttonSize = ButtonSizeInterface::BUTTON_SIZES_TABLE[UI::getInstance()->getBoostrapVersion()][$size];

        return $this;
    }

    /**
     * @param string $size
     * @throws UI_Exception
     */
    protected function requireValidSize(string $size) : void
    {
        $version = UI::getInstance()->getBoostrapVersion();

        if(!isset(ButtonSizeInterface::BUTTON_SIZES_TABLE[$version]))
        {
            throw new UI_Exception(
                'Unknown bootstrap version',
                sprintf(
                    'No button sizes known for bootstrap version [%s].',
                    $version
                ),
                ButtonSizeInterface::ERROR_UNKNOWN_BOOTSTRAP_SIZE_VERSION
            );
        }

        if(isset(ButtonSizeInterface::BUTTON_SIZES_TABLE[$version][$size])) {
            return;
        }

        throw new UI_Exception(
            'Unknown button size',
            sprintf(
                'Button size [%s] not known for bootstrap version [%s].',
                $size,
                $version
            ),
            ButtonSizeInterface::ERROR_UNKNOWN_BOOTSTRAP_SIZE
        );
    }

    public function getSizeClass() : ?string
    {
        if(empty($this->buttonSize)) {
            return null;
        }

        return 'btn-'.$this->buttonSize;
    }
}
