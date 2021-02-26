<?php

class Application_FilterSettings_DateParser
{
    protected $string;
    
    protected $errorMessage;
    
    protected $commands;
    
    public function __construct($dateString)
    {
        $this->string = trim($dateString);
        
        if(!$this->isEmpty()) {
            $this->parse();
        }
    }

    public function isEmpty()
    {
        return empty($this->string);
    }
    
    protected function parse()
    {
        $string = mb_strtoupper($this->string);
    
        $specials = array(
            'TODAY' => mb_strtoupper(t('TODAY')),
            'YESTERDAY' => mb_strtoupper(t('YESTERDAY')),
        );
        
        // replace special strings with their date equivalents
        foreach($specials as $special => $translated) 
        {
            $string = str_replace($translated, $special, $string);
            
            if(strstr($string, $special)) 
            {
                $replace = '';
                
                switch($special) {
                    case 'TODAY':
                        $replace = date('Y-m-d');
                        break;
    
                    case 'YESTERDAY':
                        $dt = new DateTime();
                        $dt->add(DateInterval::createFromDateString('yesterday'));
                        $replace = $dt->format('Y-m-d');
                        break;
                }
    
                $string = str_replace($special, $replace, $string);
            }
        }
        
        $matches = array();
        preg_match_all('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})[ ]+([0-9]{1,2}):([0-9]{1,2})|([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $string, $matches, PREG_PATTERN_ORDER);
        if(!isset($matches[0]) || empty($matches[0])) {
            $this->setError(t('No valid date recognized.'));
            return;
        }
    
        $dates = array();
        $cnt = 1;
        $validSymbols = array();
        foreach($matches[0] as $dateString) {
            try{
                $date = new DateTime($dateString);
            } catch(Exception $e) {
                $this->setError(t('The date %1$s does not exist.', $dateString));
                return;
            }
            
            $placeholder = '_DT'.$cnt.'_';
            $string = str_replace($dateString, ' '.$placeholder.' ', $string);
            $validSymbols[] = $placeholder;
            
            if(strstr($dateString, ':')) {
                $hasTime = true;
                $dateString = $date->format('Y-m-d H:i');
            } else {
                $hasTime = false;
                $dateString = $date->format('Y-m-d');
            }
            
            $dates[$placeholder] = array(
                'date' => $date,
                'dateString' => $dateString,
                'hasTime' => $hasTime
            );
            
            $cnt++;
        }
        
        $tokens = array(
            'FROM' => mb_strtoupper(t('FROM')),
            'TO' => mb_strtoupper(t('TO'))
        );
    
        foreach($tokens as $token => $translated) {
            $string = str_replace($translated, $token, $string);
            $string = str_replace($token, ' '.$token.' ', $string); // add spaces
            $validSymbols[] = $token;
        }
    
        $pieces = explode(' ', $string);
        $parts = array();
        foreach($pieces as $part) {
            $part = trim($part);
            
            // keep only known symbols and throw away everything else
            if(empty($part) || !in_array($part, $validSymbols)) {
                continue;
            }
            
            $parts[] = $part;
        }
    
        $commands = array();
        $currentToken = null;
        foreach($parts as $part) {
            if(isset($tokens[$part])) {
                $currentToken = $part;
                continue;
            } 
            
            if(isset($dates[$part])) 
            {
                $def = $dates[$part];
                
                switch($currentToken) {
                    case 'FROM':
                        $start = $def['dateString'].':00';
                        if(!$def['hasTime']) {
                            $start = $def['dateString'] . ' 00:00:00';
                        }
                        $sql = sprintf(" >= '%s'", $start);
                        break;
                        
                    case 'TO':
                        $end = $def['dateString'].':59';
                        if(!$def['hasTime']) {
                            $end = $def['dateString'] . ' 23:59:59';
                        }
                        $sql = sprintf(" <= '%s'", $end);
                        break;
                        
                    default:
                        if(!$def['hasTime']) {
                            $start = $def['dateString'] . ' 00:00:00';
                            $end = $def['dateString'] . ' 23:59:59';
                        } else {
                            $start = $def['dateString'] . ':00';
                            $end = $def['dateString'] . ':59';
                        }
                        $sql = sprintf(" BETWEEN '%s' AND '%s'", $start, $end);
                        break;
                }
                
                $commands[] = array(
                    'token' => $currentToken,
                    'date' => $def['date'], 
                    'dateString' => $def['dateString'],
                    'sql' => $sql
                );
            }
        }

        if(empty($parts)) {
            $this->setError(t('No valid date search could be recognized.'));
            return;
        }
        
        $this->commands = $commands;
        
        /*
        echo '<pre style="background:#fff;color:#666;padding:6px;border:solid 1 px #ccc;">';
        print_r(array(
            'string' => $this->string,
            'datified' => $datified,
            'matches' => $matches,
            'parsable' => $string,
            'validSymbols' => $validSymbols,
            'parts' => $parts,
            'commands' => $commands
        ));
        echo '</pre>';
        */
    }
    
    protected function setError($message)
    {
        $this->errorMessage = $message;
    }
    
    public function isValid()
    {
        return !isset($this->errorMessage);
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    public function getDates()
    {
        return $this->commands;
    }
}