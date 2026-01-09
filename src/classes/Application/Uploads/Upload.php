<?php
/**
 * File containing the {@link Application_Uploads_Upload} class.
 *
 * @package Application
 * @subpackage Uploads
 * @see Application_Uploads_Upload
 */

use Application\AppFactory;
use Application\Application;
use Application\Media\DocumentTrait;

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
    use DocumentTrait;

    public const ERROR_NO_TRANSACTION_STARTED = 532001;
    public const ERROR_NO_MATCHING_DOCUMENT_FOUND = 532002;
    
    protected Application_Uploads $uploads;
    protected int $id;

    /**
     * All data from the database record for this upload.
     * @var array<string,mixed>
     */
    protected array $data = array();

    public function __construct(int $upload_id)
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

    public function getDateAdded() : DateTime
    {
        return new DateTime($this->data['upload_date']);
    }

    /**
     * Retrieves the full path to the uploaded file in the temporary storage folder.
     * @return string
     */
    public function getPath() : string
    {
        return $this->uploads->getStorageFolder() . '/' . $this->id . '.' . $this->getExtension();
    }

    /**
     * Retrieves the extension of the file. Always lowercase.
     * @return string
     */
    public function getExtension() : string
    {
        return $this->data['upload_extension'];
    }
    
    /**
     * Retrieves the name of the uploaded file.
     * @return string
     */
    public function getName() : string
    {
        return $this->data['upload_name'];
    }

    public function getUserID() : int
    {
        return (int)$this->data['user_id'];
    }

    /**
     * Retrieves the ID of the upload.
     * @return int
     */
    public function getID() : int
    {
        return $this->id;
    }

    public function getMediaSourceID(): string
    {
        return Application_Uploads::MEDIA_TYPE;
    }

    public function getMediaPrimaryName(): string
    {
        return Application_Uploads::PRIMARY_NAME;
    }

    public function isUpload() : bool
    {
        return true;
    }
    
    public function upgrade() : Application_Media_Document
    {
        return Application_Media::getInstance()->createFromUpload($this);
    }

    public function delete() : void
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
                `upload_id`=:primary",
            array(
                'primary' => $this->id
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
            Application_Uploads::TABLE_NAME,
            array(
                Application_Media::PRIMARY_NAME => $document->getID(),
                Application_Uploads::PRIMARY_NAME => $this->getID()
            ),
            array(Application_Uploads::PRIMARY_NAME)
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
        $id = $this->data[Application_Media::PRIMARY_NAME] ?? 0;
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
        
        return AppFactory::createMedia()->idExists($media_id);
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
           return AppFactory::createMedia()->getByID($this->getDocumentID());
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

    public function isImage() : bool
    {
        return in_array($this->getExtension(), Application_Media_Document_Image::IMAGE_EXTENSIONS);
    }

    public function getMaxThumbnailSize(): int
    {
        if($this->isImage()) {
            return $this->getThumbnailSourceImage()->getWidth();
        }

        return Application_Media_DocumentInterface::DEFAULT_TYPE_ICON_THUMBNAIL_SIZE;
    }

    public function getThumbnailSourcePath(): string
    {
        if($this->isImage()) {
            return $this->getPath();
        }

        return $this->getTypeIconPath();
    }
}