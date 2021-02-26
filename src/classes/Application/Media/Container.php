<?php
/**
 * File containing the {@link Application_Media_Container} interface.
 * @package Application
 * @subpackage Media
 * @see Application_Media_Container
 */

/**
 * Interface for media containers. Media containers can describe
 * their media content to allow for automatic media processing
 * using the media processor class. This is mainly used for automatic
 * image preprocessing according to image size presets.
 * 
 * @package Application
 * @subpackage Media
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Media_Processor
 * @see Application_Media_Processor::addContainer()
 */
interface Application_Media_Container
{
   /**
    * Retrieves a list of media documents stored within this
    * media container, as an indexed array with document instances.
    * 
    * NOTE: Each document must have its media configuration set
    * so it can be processed properly.
    * 
    * @return Application_Media_Document[]
    */
    public function getMediaDocuments();
    
   /**
    * Checks whether all available media documents have been 
    * properly prepared/processed, and as such can be considered
    * complete.
    * 
    * @return boolean
    */
    public function isMediaPrepared();
}