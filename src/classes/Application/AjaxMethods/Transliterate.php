<?php

use AppUtils\ConvertHelper;

class Application_AjaxMethods_Transliterate extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'Transliterate';
    public const int ERROR_EMPTY_STRING_SPECIFIED = 14801;
    
    protected string $defaultSpaceChar = '-';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $response = array(
            'string' => ConvertHelper::transliterate(
                $this->string, 
                $this->spaceChar, 
                $this->lowercase
            )
        );
        
        $this->sendResponse($response);
    }
    
    protected bool $lowercase;
    
    protected string $string;
    
    protected string $spaceChar;
    
    protected function validateRequest() : void
    {
        $this->request->registerParam('lowercase')->setEnum('true', 'false');
        $this->request->registerParam('spaceCharacter')->setEnum('-', '_');
        
        $string = (string)$this->request->getParam('string');
        if(empty($string)) {
            $this->sendError(t('Empty string.'), null, self::ERROR_EMPTY_STRING_SPECIFIED);
        }
        
        $this->string = strip_tags($string);
        
        $this->lowercase  = $this->request->getBool('lowercase');
        
        $this->spaceChar = (string)$this->request->getParam('spaceCharacter', $this->defaultSpaceChar);
        if(empty($this->spaceChar)) {
            $this->spaceChar = $this->defaultSpaceChar;
        }
    }
}