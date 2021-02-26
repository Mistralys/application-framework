<?php

require_once 'Application/Media.php';

class Application_AjaxMethods_ProcessMediaDocument extends Application_AjaxMethod
{
    public function processJSON()
    {
        $this->request->registerParam('document_id')->setInteger();
        $this->request->registerParam('config_id')->setInteger();
        
        $documentID = intval($this->request->getParam('document_id'));
        $configID = $this->request->getParam('config_id');
        $media = Application_Media::getInstance();
        
        if(empty($documentID) || !$media->idExists($documentID)) {
            $this->sendErrorUnknownElement(t('Document'));
        }
        
        if(empty($configID) || !$media->configurationIDExists($configID)) {
            $this->sendErrorUnknownElement(t('Media configuration'));
        }
        
        $status = 'success';
        $message = null;
        
        try{
            $document = $media->getByID($documentID);
            $config = $media->getConfigurationByID($configID);
            $config->process($document);
        }
        catch(Exception $e)
        {
            $status = 'failure';
            $message = t('An exception occurred with code #%s:', $e->getCode()).' '.$e->getMessage();
        }
        
        $this->sendResponse(array(
            'status' => $status,
            'message' => $message,
            'document_id' => $documentID,
            'config_id' => $configID
        ));
    }
}