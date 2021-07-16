<?php

declare(strict_types=1);

class Application_ErrorDetails
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $abstract;

    /**
     * @var string
     */
    protected $themePath;

    /**
     * @var string
     */
    protected $themeURL;

    /**
     * @var string
     */
    protected $sentContent;

    /**
     * @var Throwable
     */
    protected $exception;

    /**
     * @var bool
     */
    protected $develinfo = false;
    
   /**
    * @var string[]
    */
    protected $themeLocations;
    
   /**
    * @var string
    */
    protected $contentType;
    
    public function __construct(string $title, string $abstract, string $themePath, string $themeURL, array $themeLocations, string $sentContent, string $contentType, Throwable $e, bool $develinfo=false)
    {
        $this->title = $title;
        $this->abstract = $abstract;
        $this->themePath = $themePath;
        $this->themeURL = $themeURL;
        $this->sentContent = $sentContent;
        $this->exception = $e;
        $this->develinfo = $develinfo;
        $this->contentType = $contentType;
        $this->themeLocations = $themeLocations;
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getAbstract() : string
    {
        return $this->abstract;
    }
    
    public function getThemePath() : string
    {
        return $this->themePath;
    }
    
    public function getThemeURL() : string
    {
        return $this->themeURL;
    }
    
    public function getSentContent() : string
    {
        return $this->sentContent;
    }
    
    public function getException() : Throwable
    {
        return $this->exception;
    }
    
    public function isDeveloperInfoEnabled() : bool
    {
        return $this->develinfo;
    }
    
    public function renderException() : string
    {
        return renderExceptionInfo($this->getException(), $this->isDeveloperInfoEnabled(), $this->isHTML());
    }

    public function renderPreviousException() : string
    {
        $stack = $this->getExceptionStack();
        $content = '';

        foreach ($stack as $exception)
        {
            $content .=
                renderExceptionInfo($exception, $this->isDeveloperInfoEnabled(), $this->isHTML(), false).
                '<h4 class="errorpage-header">Stack trace</h4>'.
                renderTrace($exception);
        }

        return $content;
    }

    public function getExceptionStack() : array
    {
        return $this->getExceptionStackRecursive($this->getException());
    }

    /**
     * @param Throwable $exception
     * @param Throwable[] $stack
     * @return Throwable[]
     */
    private function getExceptionStackRecursive(Throwable $exception, array $stack=array()) : array
    {
        $prev = $exception->getPrevious();

        if($prev instanceof Throwable)
        {
            $stack[] = $prev;

            $stack = $this->getExceptionStackRecursive($prev, $stack);
        }

        return $stack;
    }
    
    public function renderTrace() : string
    {
        return renderTrace($this->getException());
    }
    
    public function isHTML() : bool
    {
        return $this->contentType === 'html';
    }
    
    public function hasPreviousException() : ?Throwable
    {
        return $this->getException()->getPrevious();
    }
    
   /**
    * Attempts to find a file in any of the available theme locations:
    * This searches in the selected theme first -if any- and then in
    * the default theme.
    * 
    * @param string $file The relative path to the file, e.g. "img/logo-big.png"
    * @return Application_ErrorDetails_ThemeFile|NULL
    */
    public function findFile(string $file) : ?Application_ErrorDetails_ThemeFile
    {
        foreach($this->themeLocations as $location) {
            $path = $location[1].'/'.$file;
            if(file_exists($path)) {
                return new Application_ErrorDetails_ThemeFile(
                    $path,
                    $location[0].'/'.$file
                );
            }
        }
        
        return null;
    }
}

