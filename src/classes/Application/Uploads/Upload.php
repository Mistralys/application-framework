<?php
/**
 * File containing the {@link Application_Uploads_Upload} class.
 *
 * @package Application
 * @subpackage Uploads
 * @see Application_Uploads_Upload
 */

use AppUtils\ImageHelper;

/**
 * Upload object: used to represent a single uploaded file. Can
 * be retrieved using the upload manager.
 *
 * @package Application
 * @subpackage Uploads
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Uploads
 */
class Application_Uploads_Upload implements Application_Media_DocumentInterface
{
    use Application_Traits_Loggable;

    public const ERROR_NO_TRANSACTION_STARTED = 532001;
    public const ERROR_NO_MATCHING_DOCUMENT_FOUND = 532002;
    
    /**
     * @var Application_Uploads
     */
    protected $uploads;

    /**
     * The ID of the uploaded file.
     * @var int
     */
    protected $id;

    /**
     * All data from the database record for this upload.
     * @var array
     */
    protected $data = array();

    public function __construct($upload_id)
    {
        $data = DBHelper::fetch(
            "SELECT
                `user_id`,
                `upload_date`,
                `upload_extension`,
                `upload_name`,
                `media_id`
            FROM
                `uploads`
            WHERE
                `upload_id`=:upload_id",
            array(
                ':upload_id' => $upload_id
            )
        );

        if (!is_array($data) || !isset($data['user_id'])) {
            throw new Application_Exception(
                'Unknown upload',
                sprintf(
                    'No such upload with ID [%1$s] found in the database.',
                    $upload_id
                )
            );
        }
        
        $this->id = $upload_id;
        $this->data = $data;
        $this->uploads = Application_Uploads::getInstance();
    }

    public function getDateAdded()
    {
        return new DateTime($this->data['upload_date']);
    }

    /**
     * Retrieves the full path to the uploaded file in the temporary storage folder.
     * @return string
     */
    public function getPath()
    {
        return $this->uploads->getStorageFolder() . '/' . $this->id . '.' . $this->getExtension();
    }

    /**
     * Retrieves the extension of the file. Always lowercase.
     * @return string
     */
    public function getExtension()
    {
        return $this->data['upload_extension'];
    }
    
    public function getFilename()
    {
        return $this->getName().'.'.$this->getExtension();
    }

    /**
     * Retrieves the name of the uploaded file.
     * @return string
     */
    public function getName()
    {
        return $this->data['upload_name'];
    }

    /**
     * Retrieves the user that created the upload.
     * @return Application_User
     */
    public function getUser()
    {
        $user = Application::getUser();
        return $user->createByID($this->data['user_id']);
    }

    /**
     * Retrieves the ID of the upload.
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    public function getThumbnailURL($width = null, $height = null)
    {
        return APP_URL . '/media.php?' . http_build_query(array(
            'source' => 'upload',
            'upload_id' => $this->id,
            'width' => $width,
            'height' => $height
        ));
    }

    protected function getThumbnailPath($width = null, $height = null)
    {
        if($this->isTypeSVG()) {
            return $this->getPath();
        }
        
        if (empty($width) && empty($height)) {
            return $this->getPath();
        }

        $folder = dirname($this->getPath());

        if (empty($width)) {
            return sprintf(
                '%s/%s_h%s.%s',
                $folder,
                $this->id,
                $height,
                $this->getExtension()
            );
        }

        if (empty($height)) {
            return sprintf(
                '%s/%s_w%s.%s',
                $folder,
                $this->id,
                $width,
                $this->getExtension()
            );
        }

        return sprintf(
            '%s/%s_w%s_h%s.%s',
            $folder,
            $this->id,
            $width,
            $height,
            $this->getExtension()
        );
    }
    
    public function isTypeSVG()
    {
        return strtolower($this->getExtension()) == 'svg';
    }

    public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request)
    {
        $width = intval($request->getParam('width'));
        $height = intval($request->getParam('height'));
        
        $targetFile = $this->getThumbnailPath($width, $height);

        if (!file_exists($targetFile))
        {
            $helper = ImageHelper::createFromFile($this->getPath());
            $helper->resample($width, $height);
            $helper->save($targetFile);
        }

        ImageHelper::displayImage($targetFile);

        Application::exit($this->getLogIdentifier().' | Sent document contents to stdout.');
    }

    /**
     * Retrieves the size of the media file on disk, in bytes.
     * @return int
     */
    public function getFilesize()
    {
        $size = filesize($this->getPath());
        if($size !== false) {
            return $size;
        }

        return 0;
    }

    /**
     * Retrieves the size of the media file in a human redable format,
     * e.g. 15 Kb.
     *
     * @return string
     */
    public function getFilesizeReadable()
    {
        return AppUtils\ConvertHelper::bytes2readable($this->getFilesize());
    }

    public function isUpload()
    {
        return true;
    }
    
    public function upgrade()
    {
        require_once 'Application/Media.php';
        $media = Application_Media::getInstance();
        return $media->createFromUpload($this);
    }

    public function delete()
    {
        $this->log('Requested to delete.');
        
        if(!DBHelper::isTransactionStarted()) {
            throw new Application_Exception(
                'No transaction started',
                'A database transaction needs to be present to delete a media document.',
                self::ERROR_NO_TRANSACTION_STARTED
            );
        }
        
        $path = $this->getPath();
        if(file_exists($path) && !Application::isSimulation()) {
            @unlink($path);
        }
        
        DBHelper::delete(
            "DELETE FROM
                `uploads`
            WHERE
                `upload_id`=:upload_id",
            array(
                'upload_id' => $this->id
            )    
        );
    }
    
    public function getLogIdentifier() : string
    {
        return sprintf(
            'Media document [#%s] | Name [%s] | Size [%s]',
            $this->getID(),
            $this->getFilename(),
            $this->getFilesizeReadable()
        );
    }

    public function isVector()
    {
        return $this->isTypeSVG();
    }
    
   /**
    * Sets the media document that has been created using this
    * upload. This usually means that the upload will soon be
    * removed automatically.
    * 
    * @param Application_Media_Document $document
    */
    public function setDocument(Application_Media_Document $document)
    {
        if($this->hasDocument()) 
        {
            if($this->getDocumentID() == $document->getID()) {
                return;
            }
            
            throw new Application_Exception(
                'Cannot set the upload\'s document, it already has one.',
                sprintf(
                    'Cannot set document [%s] for upload [%s], it already uses document [%s].',
                    $document->getID(),
                    $this->getID(),
                    $this->getDocumentID()
                )
            );
        }
        
        DBHelper::requireTransaction('Setting an upload\'s document');
        
        DBHelper::updateDynamic(
            'uploads', 
            array(
                'media_id' => $document->getID(),
                'upload_id' => $this->getID()
            ),
            array('upload_id')
        );
    }
    
   /**
    * Retrieves the media document ID the upload is connected to,
    * if any has been set.
    * 
    * @return int|NULL
    */
    public function getDocumentID() :?int
    {
        $id = $this->data['media_id'];
        if(!empty($id)) {
            return (int)$id;
        }
        
        return null;
    }
    
   /**
    * Whether the upload has been turned into a media document yet.
    * @return bool
    */
    public function hasDocument() : bool
    {
        $media_id = $this->getDocumentID();
        if($media_id === null) {
            return false;
        }
        
        return Application::createMedia()->idExists($media_id);
    }
    
   /**
    * Retrieves the media document connected to the upload.
    * Note: this will throw an exception if the upload has
    * no media document. Use the {@link Application_Uploads_Upload::hasDocument()}
    * method to check this beforehand.
    * 
    * @throws Application_Exception
    * @return Application_Media_Document
    * @see Application_Uploads_Upload::hasDocument()
    */
    public function getDocument() : Application_Media_Document
    {
        if($this->hasDocument()) {
           return Application::createMedia()->getByID($this->getDocumentID()); 
        }
        
        throw new Application_Exception(
            'The upload has no matching media document.',
            sprintf(
                'Cannot retrieve media document for upload [%s]: it has no connected document, or the document does not exist anymore.',
                $this->getID()
            ),
            self::ERROR_NO_MATCHING_DOCUMENT_FOUND
        );
    }
}