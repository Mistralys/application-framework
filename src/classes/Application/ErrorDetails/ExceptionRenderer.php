<?php

declare(strict_types=1);

namespace Application\ErrorDetails;

use AppUtils\Interfaces\StringableInterface;
use Throwable;

class ExceptionRenderer implements StringableInterface
{
    private Throwable $exception;
    private bool $develinfo;

    private string $contentType;

    public function __construct(Throwable $e, bool $develinfo=false)
    {
        $this->exception = $e;
        $this->develinfo = $develinfo;
        $this->contentType = 'html';

        if(!isContentTypeHTML())
        {
            $this->contentType = 'txt';
        }
    }

    public function getException() : Throwable
    {
        return $this->exception;
    }

    public function isDeveloperInfoEnabled() : bool
    {
        return $this->develinfo;
    }

    public function getContentType() : string
    {
        return $this->contentType;
    }

    public function isHTML() : bool
    {
        return $this->contentType === 'html';
    }

    public function renderException() : string
    {
        return renderExceptionInfo(
            $this->getException(),
            $this->isDeveloperInfoEnabled(),
            $this->isHTML()
        );
    }

    public function renderPreviousException() : string
    {
        $stack = $this->getExceptionStack();
        $content = '';
        $isHTML = $this->isHTML();

        foreach ($stack as $exception)
        {
            $content .= sb()
                ->add(renderExceptionInfo($exception, $this->isDeveloperInfoEnabled(), $isHTML, false))
                ->ifTrue($isHTML, '<h4 class="errorpage-header">Stack trace</h4>')
                ->add(renderTrace($exception));
        }

        return $content;
    }

    /**
     * @return Throwable[]
     */
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

    public function hasPreviousException() : ?Throwable
    {
        return $this->getException()->getPrevious();
    }

    public function renderStack() : string
    {
        $output = '';

        foreach($this->getExceptionStack() as $exception)
        {
            $output .=
                renderExceptionInfo($exception, $this->isDeveloperInfoEnabled(), $this->isHTML(), false).
                renderTrace($exception);
        }

        return $output;
    }

    public function __toString() : string
    {
        return $this->renderStack();
    }
}
