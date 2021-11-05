<?php

class Application_AjaxMethods_Transliterate extends Application_AjaxMethod
{
    public const ERROR_EMPTY_STRING_SPECIFIED = 14801;
    
    protected $defaultSpaceChar = '-';
    
    public function processJSON()
    {
        $response = array(
            'string' => AppUtils\ConvertHelper::transliterate(
                $this->string, 
                $this->spaceChar, 
                $this->lowercase
            )
        );
        
        $this->sendResponse($response);
    }
    
    protected $lowercase;
    
    protected $string;
    
    protected $spaceChar;
    
    protected function validateRequest()
    {
        $this->request->registerParam('lowercase')->setEnum('true', 'false');
        $this->request->registerParam('spaceCharacter')->setEnum('-', '_');
        
        $string = $this->request->getParam('string');
        if(empty($string)) {
            $this->sendError(t('Empty string.'), null, self::ERROR_EMPTY_STRING_SPECIFIED);
        }
        
        $this->string = strip_tags($string);
        
        $this->lowercase  = $this->request->getBool('lowercase');
        
        $this->spaceChar = $this->request->getParam('spaceCharacter', $this->defaultSpaceChar);
        if(empty($this->spaceChar)) {
            $this->spaceChar = $this->defaultSpaceChar;
        }
    }
}