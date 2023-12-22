<?php

declare(strict_types=1);

namespace UI\Interfaces;

use AppUtils\Interfaces\StringableInterface;

interface CapturableInterface
{
    public function startCapture(): self;
    public function endCapture(): self;
    public function endCaptureAppend(): self;

    /**
     * @param string|number|StringableInterface|NULL $content
     * @return self
     */
    public function setContent($content): self;

    /**
     * @param string|number|StringableInterface|NULL $content
     * @return self
     */
    public function appendContent($content): self;
    public function getContent(): string;
}
