<?php

class Application_AjaxMethods_AddMessage extends Application_AjaxMethod
{
    public function processJSON()
    {
        $ui = UI::getInstance();
        $ui->addMessage($this->message, $this->type);

        $payload = array(
            'success' => true
        );

        $this->sendResponse($payload);
    }
    
   /**
    * @var string
    */
    protected $message;
    
   /**
    * @var string
    */
    protected $type;
    
    protected function validateRequest()
    {
        $this->type = $this->request->registerParam('type')->setEnum('error', 'success', 'info')->get();
        $this->message = $this->request->getParam('message');
        
        if (empty($this->type)) {
            $this->sendError(t('Invalid message type'));
        }
    }
}