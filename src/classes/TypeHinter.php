<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class TypeHinter
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var array<string,string>
     */
    private $methods = array();

    /**
     * @var string[]
     */
    private $methodNames = array();

    /**
     * @var string
     */
    private $fileSuffix;

    /**
     * @var array<string,string>
     */
    private $replaces = array();

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function setFileSuffix(string $suffix) : TypeHinter
    {
        $suffix = ltrim($suffix, '.');

        if(!empty($suffix))
        {
            $suffix = '.'.$suffix;
        }

        $this->fileSuffix = $suffix;
        return $this;
    }

    public function addMethod(string $methodName, string $type) : TypeHinter
    {
        $this->methods[$methodName] = array(
            'regex' => sprintf(
                '/function[ ]*%s\(([^{]*)\)\s*{/sU',
                preg_quote($methodName, '/')
            ),
            'type' => $type
        );
        return $this;
    }

    public function addReplace(string $search, string $replace) : TypeHinter
    {
        $this->replaces[$search] = $replace;
        return $this;
    }

    public function process() : TypeHinter
    {
        $files = $this->getFilesList();

        $this->methodNames = array_keys($this->methods);

        foreach($files as $file)
        {
            $this->processFile($file);
        }

        return $this;
    }

    public function getFilesList() : array
    {
        return FileHelper::createFileFinder($this->rootPath)
            ->makeRecursive()
            ->setPathmodeAbsolute()
            ->getPHPFiles();
    }

    private function processFile(string $file) : void
    {
        $content = FileHelper::readContents($file);

        foreach($this->methodNames as $name)
        {
            $content = $this->replaceMethod($name, $content);
        }

        FileHelper::saveFile($file.$this->fileSuffix, $content);
    }

    private function replaceMethod(string $name, string $content) : string
    {
        $matches = array();
        preg_match_all($this->methods[$name]['regex'], $content, $matches);

        $replaces = array();

        foreach($matches[0] as $idx => $matchedText)
        {
            $replaces[$matchedText] = $this->generateMethod($name, $this->methods[$name]['type'], $matches[1][$idx]);
        }

        $content = str_replace(array_keys($replaces), array_values($replaces), $content);

        return str_replace(array_keys($this->replaces), array_values($this->replaces), $content);
    }

    private function generateMethod(string $name, string $type, string $paramsString) : string
    {
        return sprintf(
            'function %s(%s) : %s%s    {',
            $name,
            $paramsString,
            $type,
            PHP_EOL
        );
    }
}
