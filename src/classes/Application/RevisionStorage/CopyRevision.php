<?php

abstract class Application_RevisionStorage_CopyRevision
{
    public const ERROR_CLASS_MISMATCH_FOR_TARGET_REVISIONABLE = 720001;
    
    protected $sourceRevision;
    
    protected $targetRevision;
    
    protected $ownerID;
    
    protected $ownerName;
    
    protected $comments;
    
    /**
     * @var Application_RevisionableStateless
     */
    protected $revisionable;
    
    /**
     * @var Application_RevisionableStateless
     */
    protected $targetRevisionable;
    
    protected $date;
    
   /**
    * @var Application_RevisionStorage
    */
    protected $storage;
    
    public function __construct(Application_RevisionStorage $storage, Application_RevisionableStateless $revisionable, $sourceRevision, $targetRevision, $ownerID, $ownerName, $comments, DateTime $date=null)
    {
        if(!$date) {
            $date = new DateTime();
        }
        
        $this->storage = $storage;
        $this->revisionable = $revisionable;
        $this->sourceRevision = $sourceRevision;
        $this->targetRevision = $targetRevision;
        $this->ownerID = $ownerID;
        $this->ownerName = $ownerName;
        $this->comments = $comments;
        $this->date = $date;
        
        $this->init();
    }
    
    protected function init()
    {
        
    }
    
    /**
     * Sets a target revisionable to copy the revision to.
     * @param Application_RevisionableStateless $revisionable
     */
    public function setTarget(Application_RevisionableStateless $revisionable)
    {
        $sourceType = $this->revisionable->getRevisionableTypeName();
        $targetType = $revisionable->getRevisionableTypeName();
        
        if($sourceType != $targetType) {
            throw new Application_Exception(
                'Not a valid revisionable',
                sprintf(
                    'The target revisionable type [%s] does not match that of the revisionable to copy from: [%s].',
                    $targetType,
                    $sourceType
                ),
                self::ERROR_CLASS_MISMATCH_FOR_TARGET_REVISIONABLE
            );
        }
    
        $this->targetRevisionable = $revisionable;
        $this->targetRevision = $revisionable->getRevision();
    
        $this->log(sprintf(
            'Set the target revisionable to [%s] in revision [%s].',
            get_class($revisionable),
            $this->targetRevision
        ));
    }
    
    abstract protected function getParts();
    
    public function process()
    {
        $this->log('Starting copy.');
        
        // if no target revisionable object has been set to copy the 
        // revision to, we use the source revisionable to create a
        // copy within the same object.
        if(!isset($this->targetRevisionable)) {
            $this->targetRevisionable = $this->revisionable;
        }

        if($this->storage->hasRevdata()) {
            $this->processRevdata();
        }
        
        $this->processParts();
        
        $this->log('Copy complete.');
    }
    
    protected function processParts()
    {
        $parts = $this->getParts();
        foreach($parts as $part) {
            $method = 'copy'.ucfirst($part);
            $this->$method();
        }
    }
    
    protected function processRevdata()
    {
        if(!$this->storage->hasRevdata()) {
            $this->log('The revisionable has no revdata, skipping.');
            return;
        }
        
        $this->log('Processing the revisionable\'s revdata.');
        
        $this->_processRevdata();
    }
    
    abstract protected function _processRevdata();
        
    protected function log($message)
    {
        Application::log(sprintf(
            '%4$s RevisionCopy | [v%1$s] to [v%2$s] | %3$s',
            $this->sourceRevision,
            $this->targetRevision,
            $message,
            $this->revisionable->getRevisionableTypeName()
        ));
    }

    protected $debug = false;
    
    protected function enableDebug($enable=true)
    {
        $this->debug = $enable;
        return $this;
    }
}