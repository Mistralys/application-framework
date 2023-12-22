<?php

declare(strict_types=1);

namespace UI\Traits;

use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use UI_Exception;

trait CapturableTrait
{
    protected bool $capturing = false;

    /**
     * Starts output buffering to capture the content to use for the section's body.
     * @return $this
     * @throws OutputBuffering_Exception
     * @see self::endCapture()
     */
    public function startCapture() : self
    {
        if(!$this->capturing) {
            $this->capturing = true;
            OutputBuffering::start();
        }

        return $this;
    }

    /**
     * Stops the output buffering started with {@link self::startCapture()}.
     *
     * @return $this
     * @throws OutputBuffering_Exception
     * @throws UI_Exception
     */
    public function endCapture() : self
    {
        if($this->capturing) {
            $this->setContent(OutputBuffering::get());
            $this->capturing = false;
        }

        return $this;
    }

    /**
     * Like {@see self::endCapture()}, but appends the captured content
     * to any existing content in the section.
     *
     * @return $this
     * @throws OutputBuffering_Exception
     * @throws UI_Exception
     */
    public function endCaptureAppend() : self
    {
        if($this->capturing) {
            $this->appendContent(OutputBuffering::get());
            $this->capturing = false;
        }

        return $this;
    }
}
