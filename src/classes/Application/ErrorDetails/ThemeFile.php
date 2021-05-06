<?php

declare(strict_types=1);

class Application_ErrorDetails_ThemeFile
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    public function __construct(string $path, string $url)
    {
        $this->path = $path;
        $this->url = $url;
    }

    public function getURL() : string
    {
        return $this->url;
    }

    public function getPath() : string
    {
        return $this->path;
    }
}
