<?php

use AppUtils\FileHelper\FileInfo;
use AppUtils\ImageHelper;

interface Application_Media_DocumentInterface extends Application_Interfaces_Loggable
{
    public const DEFAULT_THUMBNAIL_SIZE = 128;
    public const DEFAULT_TYPE_ICON_THUMBNAIL_SIZE = 64;

    /**
     * Retrieves the ID of the document.
     * @return int
     */
    public function getID() : int;
    
   /**
    * Whether this document is an uploaded document, which
    * has not been transformed into a full media document.
    * 
    * @return boolean
    */
    public function isUpload() : bool;
    
    /**
     * Retrieves the name of the uploaded file, without extension.
     * @return string
     */
    public function getName() : string;

    /**
     * Retrieves the media document's filename, e.g.:
     *
     * <code>media_name.jpg</code>
     *
     * Note that this is NOT the filename on disk, but the
     * filename as defined by the user that should be used
     * when the media file is downloaded or used in the exports.
     *
     * @return string
     */
    public function getFilename() : string;
    
    /**
     * Retrieves the full path to the uploaded file in the temporary storage folder.
     * @return string
     */
    public function getPath() : string;

    /**
     * Gets the image that should be used to generate the
     * document's thumbnail.
     *
     * @return string The absolute path to the file on disk.
     */
    public function getThumbnailSourcePath() : string;

    public function getThumbnailSourceImage() : ImageHelper;

    /**
     * Retrieves the full URL to the media script to display a thumbnail
     * of the media file. The width and height parameters can be set as
     * needed to resample the thumbnail to the target size.
     *
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function getThumbnailURL(?int $width = null, ?int $height = null) : string;

    /**
     * Retrieves the default size for thumbnails of this document type.
     *
     * @param int|NULL $preferredSize Specify a preferred size, which will be used if possible. Use 0 or NULL to ignore.
     * @return int
     */
    public function getThumbnailDefaultSize(?int $preferredSize=null): int;

    /**
     * Renders a thumbnail image HTML tag for the document.
     *
     * @param int|null $preferredSize Will be used if the document allows this size. Use 0 or NULL to ignore.
     * @return string
     */
    public function renderThumbnail(?int $preferredSize=null) : string;

    /**
     * @return bool
     * @deprecated Use {@see self::isVector()} instead.
     */
    public function isTypeSVG() : bool;

    public function getTypeIconPath() : string;

    public function getTypeIconURL() : string;

    public function isVector() : bool;

    /**
     * Retrieves the extension of the file. Always lowercase.
     * @return string
     */
    public function getExtension() : string;
    
   /**
    * Retrieves the creation date of the file.
    * @return DateTime
    */
    public function getDateAdded() : DateTime;
    
   /**
    * Retrieves the size of the file on disk, in bytes.
    * @return int
    */
    public function getFilesize() : int;

    public function getMediaSourceID() : string;

    public function getMediaPrimaryName() : string;
    
   /**
    * Retrieves a human-readable representation of the file's size.
    * @return string
    */
    public function getFilesizeReadable() : string;
    
    /**
     * Retrieves the user that created the upload.
     * @return Application_User
     */
    public function getUser() : Application_User;

    public function getUserID() : int;

    /**
     * Creates a thumbnail of the image for the specified dimensions.
     * Width and height can be omitted as needed to constrain resampling
     * to one or none of the sides.
     *
     * Returns the path to the thumbnail file when successful.
     *
     * @param int|null $width
     * @param int|null $height
     * @return string The path to the generated file.
     */
    public function createThumbnail(?int $width = null, ?int $height = null) : string;

    /**
     * Serves the media file using a configuration set via the current
     * request. For image types for example, the width and height of
     * the image can be set in the request. This is used by the media
     * delivery script.
     *
     * @param Application_Media_Delivery $delivery
     * @param Application_Request $request
     * @return never
     */
    public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request);
        
   /**
    * Upgrades the upload to a full media document.
    * Has no effect if it is already a media document. 
    * 
    * @return Application_Media_Document
    */
    public function upgrade() : Application_Media_Document;

   /**
    * Deletes the media document: removes the file(s) on disk
    * and the database entries. Note that this does not ensure
    * that any records linking to this file are updated as well:
    * this can either be done via relations in the database, or
    * manually correcting those.
    */
    public function delete() : void;

    /**
     * @param bool $forceDownload
     * @return never
     */
    public function sendFile(bool $forceDownload=false);
}