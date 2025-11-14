<?php
/**
 * File containing the {@see Application_Formable_RecordSelector} class.
 * 
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSelector
 */

declare(strict_types=1);

use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\DBHelperFilterCriteriaInterface;

/**
 * Base class for select elements that allow choosing
 * items of a DBHelper collection. Can inject the target
 * element into a formable instance.
 * 
 * Handles a number of options on how to display the element.
 * 
 * @package Application
 * @subpackage Formable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Formable_RecordSelector extends Application_Formable_Selector
{
    protected DBHelperCollectionInterface $collection;
    protected DBHelperFilterCriteriaInterface $filters;
    
    public function __construct(Application_Interfaces_Formable $formable)
    {
        parent::__construct($formable);

        $this->collection = $this->createCollection();
        $this->filters = $this->collection->getFilterCriteria();

        $this->init();
    }

    protected function init() : void
    {

    }

    abstract public function createCollection() : DBHelperCollectionInterface;
    
    abstract protected function configureFilters() : void;

    protected function getDefaultName() : string
    {
        $name = $this->collection->getRecordPrimaryName();
        
        if($this->getBoolOption('multiple')) 
        {
            $name .= 's';
        }
        
        return $name; 
    }

    public function getFilters() : DBHelperFilterCriteriaInterface
    {
        return $this->filters;
    }
    
    protected function getDefaultLabel() : string
    {
        if($this->getBoolOption('multiple'))
        {
            return $this->collection->getCollectionLabel();
        }
        
        return $this->collection->getRecordLabel();
    }
    
   /**
    * Retrieves the matching entries according to
    * the selected filter criteria.
    */
    protected function _loadEntries() : void
    {
        $this->configureFilters();
        
        $items = $this->filters->getItemsObjects();
        
        foreach ($items as $item)
        {
            $this->registerEntry(
                (string)$item->getID(),
                $item->getLabel(),
                $item
            );
        }
    }
}
