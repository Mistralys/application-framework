<?php
/**
 * File containing the {@link Application_Media_Processor} class.
 *
 * @package Application
 * @subpackage Media
 * @see Application_Media_Processor
 */

/**
 * The Media Processor is a helper class for the clientside
 * product media preparation scripts. It adds all the required
 * clientside scripts and statements for the products that
 * are added.
 *
 * @package Application
 * @subpackage Media
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 */
class Application_Media_Processor
{
    const ERROR_MISSING_MEDIA_CONFIGURATION = 660001;
    
    /**
     * @var UI
     */
    protected $ui;

    protected $redirectURL;

    protected $documents = array();
    
    public function __construct($completedRedirectURL)
    {
        $this->ui = UI::getInstance();
        $this->redirectURL = $completedRedirectURL;
    }
    
    public function renderContent()
    {
        /* @var $document Application_Media_Document */
        
        $this->ui->addProgressBar();
        $this->ui->addJavascript('media/processor.js');
        $this->ui->addJavascriptHeadVariable('Media_Processor.redirect', $this->redirectURL);
        $this->ui->addJavascriptOnload('Media_Processor.Start()');
        
        $total = count($this->documents);
        for($i=0; $i<$total; $i++) {
            $document = $this->documents[$i];
            $this->ui->addJavascriptHeadStatement(
                'Media_Processor.AddDocument',
                $document->getID(),
                $document->getConfiguration()->getID()
            );
        }
        
        return '<div id="prepare-media"></div>';
    }

    public function addContainer(Application_Media_Container $container)
    {
        $this->log(sprintf('Adding container [%s].', get_class($container)));
        
        $docs = $container->getMediaDocuments();
        $total = count($docs);
        
        $this->log(sprintf('Container has [%s] documents.', $total));
        
        for($i=0; $i<$total; $i++) {
            $this->addDocument($docs[$i]);
        }
    }
    
    public function addDocument(Application_Media_Document $document)
    {
        if(!$document->hasConfiguration()) {
            throw new Application_Exception(
                'Missing media configuration',
                sprintf(
                    'The media document [%s] of type [%s] does not have a media configuration. This is required for it to be processed.',
                    $document->getID(),
                    $document->getTypeID()    
                ),
                self::ERROR_MISSING_MEDIA_CONFIGURATION
            );
        }
        
        if($document->isProcessRequired()) {
            $this->documents[] = $document;
        }
    }

    public function getDocumentsCount()
    {
        return count($this->documents);
    }
    
    protected function log($message)
    {
        Application::log(sprintf(
            'Media processor | %s',
            $message
        ));
    }
}