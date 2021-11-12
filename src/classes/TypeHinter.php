<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\FileHelper_PHPClassInfo;

class TypeHinter implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var array<int,array{name:string,regex:string,type:string,instanceOf:string}>
     */
    private $methods = array();

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

    public function addMethod(string $methodName, string $type, string $classSearch='') : TypeHinter
    {
        $this->methods[] = array(
            'name' => $methodName,
            'regex' => sprintf(
                '/function[ ]*%s\(([^{]*)\)\s*{/sU',
                preg_quote($methodName, '/')
            ),
            'type' => $type,
            'instanceOf' => $classSearch
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
        $info = new FileHelper_PHPClassInfo($file);

        if(!$info->hasClasses())
        {
            $this->log('Ignoring file, no classes found: [%s].', $file);
            return;
        }

        $classes = $info->getClassNames();
        $class = array_shift($classes);
        $content = FileHelper::readContents($file);

        $this->log('Class [%s] | Processing methods.', $class);

        foreach($this->methods as $def)
        {
            $content = $this->replaceMethod(
                $def['name'],
                $def['regex'],
                $def['type'],
                $content,
                $class,
                $def['instanceOf']
            );
        }

        FileHelper::saveFile($file.$this->fileSuffix, $content);
    }

    private function replaceMethod(string $name, string $regex, string $type, string $content, string $class, string $classSearch) : string
    {
        // If a search term is specified, verify that the class name
        // contains the term.
        if(!empty($classSearch) && stripos($class, $classSearch) === false)
        {
            $this->log('Class [%s] | Method [%s] | Search term [%s] not found.', $class, $name, $classSearch);
            return $content;
        }

        $matches = array();
        preg_match_all($regex, $content, $matches);

        $replaces = array();

        foreach($matches[0] as $idx => $matchedText)
        {
            $replaces[$matchedText] = $this->generateMethod($name, $type, $matches[1][$idx]);
        }

        $found = count($replaces);
        if($found > 0)
        {
            $this->log('Class [%s] | Method [%s] | Found [%s] matches.', $class, $name, $found);
            $content = str_replace(array_keys($replaces), array_values($replaces), $content);
        }
        else
        {
            $this->log('Class [%s] | Method [%s] | No replace matches found.', $class,$name);
        }

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

    public function getLogIdentifier() : string
    {
        return 'TypeHinter';
    }
}
