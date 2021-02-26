<?php

interface Application_Media_DocumentInterface
{
    /**
     * Retrieves the ID of the document.
     * @return int
     */
    public function getID();
    
   /**
    * Whether this document is an uploaded document, which
    * has not been transformed into a full media document.
    * 
    * @return boolean
    */
    public function isUpload();
    
    /**
     * Retrieves the name of the uploaded file, without extension.
     * @return string
     */
    public function getName();
    
   /**
    * Retrieves the name of the uploaded file, with extension.
    * @return string
    */
    public function getFilename();
    
    /**
     * Retrieves the full path to the uploaded file in the temporary storage folder.
     * @return string
     */
    public function getPath();
    
   /**
    * Retrieves the URL to a thumbnail of the document, in the specified size.
    * @return string
    */
    public function getThumbnailURL($width = null, $height = null);
    
    /**
     * Retrieves the extension of the file. Always lowercase.
     * @return string
     */
    public function getExtension();
    
   /**
    * Retrieves the creation date of the file.
    * @return DateTime
    */
    public function getDateAdded();
    
   /**
    * Retrieves the size of the file on disk, in bytes.
    * @return number
    */
    public function getFilesize();
    
   /**
    * Retrieves a human readable representation of the file's size.
    * @return string
    */
    public function getFilesizeReadable();
    
    /**
     * Retrieves the user that created the upload.
     * @return Application_User
     */
    public function getUser();
    
    /**
     * Serves the media file using a configuration set via the current
     * request. For image types for example, the width and height of
     * the image can be set in the request. This is used by the media
     * delivery script.
     *
     * @param Application_Media_Delivery $delivery
     * @param Application_Request $request
     */
    public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request);
        
   /**
    * Upgrades the upload to a full media document.
    * Has no effect if it is already a media document. 
    * 
    * @return Application_Media_Document
    */
    public function upgrade();

   /**
    * Deletes the media document: removes the file(s) on disk
    * and the database entries. Note that this does not ensure
    * that any records linking to this file are updated as well:
    * this can either be done via relations in the database, or
    * manually correcting those.
    */
    public function delete();
}