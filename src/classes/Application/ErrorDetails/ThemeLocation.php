<?php

declare(strict_types=1);

namespace Application\ErrorDetails;

class ThemeLocation
{
    private string $path;
    private string $url;

    public function __construct(string $path, string $url)
    {
        $this->path = $path;
        $this->url = $url;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getURL(): string
    {
        return $this->url;
    }
}
