<?php

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion;

use Application_Driver;
use AppUtils\NumberInfo;

class LinkedImage
{
    private string $imageName;
    private ?NumberInfo $width;
    private string $matchedText;
    public function __construct(string $imageName, ?NumberInfo $width, string $matchedText)
    {
        $this->imageName = $imageName;
        $this->width = $width;
        $this->matchedText = $matchedText;
    }

    public function getImageName(): string
    {
        return $this->imageName;
    }

    public function getWidth(): ?NumberInfo
    {
        return $this->width;
    }

    public function getMatchedText(): string
    {
        return $this->matchedText;
    }

    public function getURL() : string
    {
        return Application_Driver::getInstance()
            ->getTheme()
            ->getImageURL('whatsnew/' . $this->getImageName());
    }

    public function renderWidth() : string
    {
        return $this->getWidth()?->toCSS() ?? '';
    }
}
