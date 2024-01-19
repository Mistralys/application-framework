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
    
    protected ?Application_RevisionableStateless $targetRevisionable = null;
    
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
    public function setTarget(Application_RevisionableStateless $revisionable) : void
    {
        $sourceType = $this->revisionable->getRevisionableTypeName();
        $targetType = $revisionable->getRevisionableTypeName();
        
        if($sourceType !== $targetType) {
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

    /**
     * @return array<int,callable|string>
     */
    abstract protected function getParts();
    
    public function process() : void
    {
        $this->log('Starting copy.');

        // if no target revisionable object has been set to copy the
        // revision to, we use the source revisionable to create a
        // copy within the same object.
        $target = $this->targetRevisionable ?? $this->revisionable;

        // store it for anyone accessing the property
        $this->targetRevisionable = $target;

        if($this->storage->hasRevdata()) {
            $this->processRevdata($target);
        }
        
        $this->processParts($target);
        
        $this->log('Copy complete.');
    }
    
    protected function processParts(Application_RevisionableStateless $targetRevisionable) : void
    {
        $parts = $this->getParts();

        foreach($parts as $part)
        {
            if(is_callable($part)) {
               $part($targetRevisionable);
               continue;
            }

            $method = 'copy'.ucfirst($part);
            $this->$method($targetRevisionable);
        }
    }
    
    protected function processRevdata(Application_RevisionableStateless $targetRevisionable) : void
    {
        if(!$this->storage->hasRevdata()) {
            $this->log('The revisionable has no revdata, skipping.');
            return;
        }
        
        $this->log('Processing the revisionable\'s revdata.');
        
        $this->_processRevdata($targetRevisionable);
    }
    
    abstract protected function _processRevdata(Application_RevisionableStateless $targetRevisionable) : void;

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