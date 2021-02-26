<?php

require_once 'Application/Uploads.php';

class Application_Bootstrap_Screen_Upload extends Application_Bootstrap_Screen
{
    public function getDispatcher()
    {
        return 'storage/upload.php';
    }
    
    protected function _boot()
    {
        $this->createEnvironment();
        
        $uploads = Application_Uploads::getInstance();
        $upload = $uploads->addFromFileUpload('file');
        
        header('Content-Type:application/json; charset=UTF-8');
        
        if(!$upload) {
            die(json_encode(array(
                'status' => 'error',
                'message' => $uploads->getLastMessage()
            )));
        }
        
        die(json_encode(array(
            'status' => 'success',
            'upload_id' => $upload->getID(),
            'file_type' => $upload->getExtension()
        )));
    }
}