<?php
/**
 * @package Application
 * @subpackage Bootstrap
 */

declare(strict_types=1);

/**
 * Bootstrap class for handling file uploads via the
 * {@see ImageUploader} element, for example.
 *
 * @package Application
 * @subpackage Bootstrap
 */
class Application_Bootstrap_Screen_Upload extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return 'storage/upload.php';
    }
    
    protected function _boot() : void
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
