<?php

class Application_Whatsnew_Version_Language_Category_Item
{
   /**
    * @var Application_Whatsnew_Version_Language_Category
    */
    protected $category;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $issue;

    /**
     * @var string
     */
    protected $rawText;

    /**
     * @var string
     */
    protected $text;
    
    public function __construct(Application_Whatsnew_Version_Language_Category $category, SimpleXMLElement $node)
    {
        $this->category = $category;
        
        $this->parse($node);
    }

    public function getWhatsnew() : Application_Whatsnew
    {
        return $this->category->getWhatsnew();
    }
    
    protected function parse(SimpleXMLElement $node)
    {
        if(isset($node['author'])) {
            $this->author = (string)$node['author'];
        }
        
        if(isset($node['issue'])) {
            $this->issue = (string)$node['issue'];
        }
        
        $this->rawText = (string)$node;
    }
    
    public function getText()
    {
        if(!isset($this->text)) {
            $this->renderText();
        }
        
        return $this->text;
    }
    
    protected function renderText()
    {
        $lines = explode("\n", $this->rawText);
        
        $keep = array();
        $total = count($lines);
        for($i=0; $i < $total; $i++)
        {
            $line = trim($lines[$i]);
            $prev = null;
            $next = null;
            
            if(isset($lines[($i-1)])) {
                $prev = $lines[($i-1)];
            }
            
            if(isset($lines[($i+1)])) {
                $next = $lines[($i+1)];
            }
            
            if(empty($line) && empty($prev)) {
                continue;
            }
            
            if(empty($line) && empty($next)) {
                continue;
            }
            
            $keep[] = $line;
        }
        
        $total = count($keep);
        $lines = array();
        $count = 0;
        for($i=0; $i < $total; $i++)
        {
            $line = $keep[$i];
            if(empty($line)) {
                $count++;
                continue;
            }
            
            if(!isset($lines[$count])) {
                $lines[$count] = array();
            }
            
            $lines[$count][] = $line;
        }
        
        $keep = array();
        foreach($lines as $tokens) {
            $keep[] = implode(' ', $tokens);
        }
        
        $this->text = trim(implode('<br><br>', $keep));

        $this->renderVariables();
        $this->renderImages();
    }

    protected function renderVariables()
    {
        $vars = array(
            'appURL' => APP_URL
        );

        foreach($vars as $var => $value)
        {
            $this->text = str_replace('$'.$var, $value, $this->text);
        }
    }

    protected function renderImages()
    {
        if(!strstr($this->text, '{')) {
            return;
        }
        
        $result = array();
        preg_match_all('/{image:[ ]*(.+)}/U', $this->text, $result, PREG_PATTERN_ORDER);
        
        if(!isset($result[1]) && isset($result[1][0])) {
            return;
        }
        
        $theme = Application_Driver::getInstance()->getTheme();
        
        foreach($result[1] as $idx => $imageName)
        {
            $search = $result[0][$idx];
            $imgURL = $theme->getImageURL('whatsnew/'.$imageName);
            $replace = '<a href="'.$imgURL.'" target="_blank" class="whatsnew-image"><img src="'.$imgURL.'"/></a>';
            $this->text = str_replace($search, $replace, $this->text);
        }
    }
    
    public function getPlainText()
    {
        $text = str_replace(array("\n", "\t"), ' ', $this->rawText);
        while(strstr($text, '  ')) {
            $text = str_replace('  ', ' ', $text);
        }
        
        return $text;
    }
    
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function hasAuthor()
    {
        return !empty($this->author);
    }
    
    public function getIssue()
    {
        return $this->issue;
    }
    
    public function hasIssue()
    {
        return !empty($this->issue);
    }
    
    public function toArray()
    {
        return array(
            'text' => $this->getWhatsnew()->getParsedown()->parse($this->getText()),
            'author' => $this->getAuthor(),
            'issue' => $this->getIssue()
        );
    }
}