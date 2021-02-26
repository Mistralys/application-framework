<?php
/**
 * File containing the {@see Application_Formable_RecordSelector} class.
 * 
 * @package Application
 * @subpackage Formable
 * @see Application_Formable_RecordSelector
 */

declare(strict_types=1);

use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;

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
abstract class Application_Formable_RecordSelector implements Interface_Optionable
{
    use Traits_Optionable;

    const DEFAULT_CATEGORY_NAME = '__default';
    
   /**
    * @var DBHelper_BaseRecord[]
    */
    protected $records = array();
    
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;
    
   /**
    * @var DBHelper_BaseFilterCriteria
    */
    protected $filters;
    
   /**
    * @var Application_Interfaces_Formable
    */
    protected $formable;
    
   /**
    * @var UI
    */
    protected $ui;
    
   /**
    * @var HTML_QuickForm2_Element_Select
    */
    protected $element;
    
    public function __construct(Application_Interfaces_Formable $formable)
    {
        $this->formable = $formable;
        $this->ui = $formable->getUI();
        $this->collection = $this->createCollection();
        $this->filters = $this->collection->getFilterCriteria();
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'max-size' => 12,
            'max-height' => 300,
            'multiselect' => false,
            'select-all' => false,
            'sorting' => false,
            'sorting-callback' => null,
            'please-select' => true,
            'please-select-label' => t('Please select...'),
            'required' => false,
            'multiple' => false,
            'empty-message' => '',
            'comment' => '',
            'label' => '',
            'name' => ''
        );
    }
    
    abstract public function createCollection();
    
    abstract protected function configureFilters() : void;
    
    abstract protected function configureEntry(Application_Formable_RecordSelector_Entry $entry) : void;
    
    /**
     * Sets the name of the form element.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->setOption('name', $name);
        return $this;
    }
    
   /**
    * Adds a "Please Select..." entry.
    * 
    * NOTE: This will not be used if multiple entries may be selected.
    * 
    * @param bool $enabled
    * @return $this
    */
    public function enablePleaseSelect(bool $enabled=true)
    {
        $this->setOption('please-select', $enabled);
        return $this;
    }
    
   /**
    * Whether to allow selecting all entries.
    * 
    * @param bool $enabled
    * @return $this
    */
    public function enableSelectAll(bool $enabled=true)
    {
        $this->setOption('select-all', $enabled);
        return $this;
    }
    
   /**
    * Sets the label of the "Please select..." entry, when this is enabled.
    * 
    * @param string $label
    * @return $this
    */
    public function setPleaseSelectLabel(string $label)
    {
        $this->setOption('please-select-label', $label);
        return $this;
    }
    
   /**
    * Sets the maximum size of the select element
    * when it is in multiple mode.
    * 
    * NOTE: Only has an effect on the standard
    * select element. The multiselect element does
    * not use a size.
    * 
    * @param int $size
    * @return $this
    */
    public function setMaxSize(int $size)
    {
        if($size >= 1) 
        {
            $this->setOption('max-size', $size);
        }
        
        return $this;
    }
    
   /**
    * The selector will use the bootstrap multiselect
    * element with filtering capability instead of a 
    * regular select element.
    * 
    * Recommended for selectors with many entries.
    * 
    * @return $this
    */
    public function makeMultiselect()
    {
        $this->setOption('multiselect', true);
        return $this;
    }
    
   /**
    * Allows selecting several entries.
    * 
    * @return $this
    */
    public function makeMultiple()
    {
        $this->setOption('multiple', true);
        return $this;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function makeRequired(bool $required=true)
    {
        $this->setOption('required', $required);
        return $this;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->setOption('label', $label);
        return $this;
    }
    
    protected function getName() : string
    {
        $name = $this->getStringOption('name');
        
        if(!empty($name)) 
        {
            return $name;
        }
        
        $name = $this->collection->getRecordPrimaryName();
        
        if($this->getBoolOption('multiple')) 
        {
            $name .= 's';
        }
        
        return $name; 
    }
    
    protected function getLabel() : string
    {
        $label = $this->getStringOption('label');
        
        if(!empty($label)) 
        {
            return $label;
        }
        
        if($this->getBoolOption('multiple')) 
        {
            return $this->collection->getCollectionLabel();
        }
        
        return $this->collection->getRecordLabel();
    }
    
   /**
    * @param string|number|UI_Renderable_Interface $comment
    * @return $this
    */
    public function setComment($comment)
    {
        $this->setOption('comment', toString($comment));
        
        return $this;
    }
    
    protected function getComment() : string
    {
        return $this->getStringOption('comment');
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setEmptyMessage(string $message)
    {
        $this->setOption('empty-message', $message);
        return $this;
    }
    
    private function getEmptyMessage() : string
    {
        $message = $this->getStringOption('empty-message');
        
        if(!empty($message)) 
        {
            return $message;
        }
        
        return $this->getDefaultEmptyMessage();
    }
    
    protected function getDefaultEmptyMessage() : string
    {
        return t('No entries are available.');
    }
    
   /**
    * Injects the select element into the formable.
    * 
    * NOTE: The returned element is not always a 
    * select element. If there are no entries available,
    * a static message will be shown instead.
    * 
    * It is up to the caller to check whether the 
    * element is empty using the <code>isEmpty()</code>
    * method. 
    * 
    * @return HTML_QuickForm2_Element
    */
    public function inject() : HTML_QuickForm2_Element
    {
        if($this->isEmpty())
        {
            $this->element = $this->injectEmpty();
        }
        else if($this->getBoolOption('multiselect')) 
        {
            $this->element = $this->injectSelect_multiselect();
        }
        else
        {
            $this->element = $this->injectSelect_default();
        }
        
        $this->postInject();
        
        return $this->element;
    }
    
    protected function postInject() : void
    {
    }
    
    public function isEmpty() : bool
    {
        return $this->filters->countItems() < 1;
    }
    
    protected function injectEmpty() : HTML_QuickForm2_Element_Select
    {
        $el = $this->formable->addElementSelect($this->getName(), $this->getLabel());
        $el->setRuntimeProperty('__selector', $this);
        $el->setAttribute('data-empty', 'yes');
        $el->setAttribute('disabled', 'disabled');
        $el->setAttribute('style', 'display:none');
        
        $el->addOption(t('Please select...'), '');
        
        $this->formable->appendElementHTML($el, $this->renderEmptyMessage());
        
        return $el;
    }
    
    protected function renderEmptyMessage() : string
    {
        return $this->ui->createMessage($this->getEmptyMessage())
        ->enableIcon()
        ->makeInfo()
        ->makeNotDismissable()
        ->render();
    }
    
    public function isRequired() : bool
    {
        return $this->getBoolOption('required');
    }
    
    protected function configureElement(HTML_QuickForm2_Element_Select $el) : void
    {
        $el->setComment($this->getComment());
        $el->setRuntimeProperty('__selector', $this);
        
        if($this->isRequired())
        {
            $this->formable->makeRequired($el);
        }
        
        if($this->getBoolOption('please-select') && !$this->getBoolOption('multiple'))
        {
            $el->addOption($this->getStringOption('please-select-label'), '');
        }
        
        if($this->getBoolOption('multiple'))
        {
            $el->setAttribute('multiple', 'multiple');
            $el->setAttribute('size', (string)$this->resolveSize());
        }
        
        $this->addEntries($el);
    }
    
    protected function addEntries(HTML_QuickForm2_Element_Select $el) : void
    {
        $entries = $this->getEntriesForSelect();

        foreach($entries as $category => $entries)
        {
            $container = $el;

            if($category !== self::DEFAULT_CATEGORY_NAME)
            {
                $container = $el->addOptgroup($category);
            }

            foreach($entries as $entry)
            {
                $container->addOption(
                    $entry->getLabel(),
                    $entry->getID(),
                    $entry->getAttributes()
                );
            }
        }
    }

    /**
     * @return array<string,Application_Formable_RecordSelector_Entry[]>
     */
    protected function getEntriesForSelect() : array
    {
        $result = array();
        $records = $this->getEntries();

        foreach ($records as $record)
        {
            $entry = new Application_Formable_RecordSelector_Entry($record);

            $this->configureEntry($entry);

            $category = $entry->getCategory();
            if(empty($category))
            {
                $category = self::DEFAULT_CATEGORY_NAME;
            }

            if(!isset($result[$category]))
            {
                $result[$category] = array();
            }

            $result[$category][] = $entry;
        }

        return $result;
    }
    
    protected function resolveSize() : int
    {
        return min($this->countEntries(), $this->getMaxSize());
    }
    
    protected function countEntries() : int
    {
        return $this->filters->countItems();
    }
    
    public function getMaxSize() : int
    {
        return $this->getIntOption('max-size');
    }
    
    protected function injectSelect_default() : HTML_QuickForm2_Element_Select
    {
        $el = $this->formable->addElementSelect($this->getName(), $this->getLabel());
        
        $this->configureElement($el);
        
        return $el;
    }
    
    protected function injectSelect_multiselect() : HTML_QuickForm2_Element_Multiselect
    {
        $el = $this->formable->addElementMultiselect($this->getName(), $this->getLabel());
        
        $this->configureElement($el);
        
        if($this->getBoolOption('select-all')) 
        {
            $el->enableSelectAll();
        }
        
        $el->enableFiltering();
        
        $el->setMaxHeight($this->getIntOption('max-height'));
        
        return $el;
    }
    
   /**
    * Retrieves the matching entries according to
    * the selected filter criteria.
    * 
    * @return DBHelper_BaseRecord[]
    */
    protected function getEntries() : array
    {
        $this->configureFilters();
        
        $items = $this->filters->getItemsObjects();
        
        return $this->sortItems($items);
    }
    
   /**
    * @param DBHelper_BaseRecord[] $items
    * @return DBHelper_BaseRecord[]
    */
    protected function sortItems(array $items) : array
    {
        if($this->getBoolOption('sorting'))
        {
            $callback = $this->resolveSortingCallback();
            
            usort($items, $callback);
        }
        
        return $items;
    }
    
   /**
    * Enables sorting the items in the selector.
    * 
    * By default, they are sorted alphabetically,
    * use the <code>setSortingCallback()</code> method
    * to customize the sorting. 
    * 
    * @param bool $enable
    * @return $this
    */
    public function enableSorting(bool $enable=true)
    {
        $this->setOption('sorting', $enable);
        return $this;
    }
    
   /**
    * Sets the sorting callback function to use to sort the
    * items in the selector.
    * 
    * @param callable $callback
    * @return $this
    */
    public function setSortingCallback($callback)
    {
        $this->setOption('sorting-callback', $callback);
        return $this;
    }
    
   /**
    * @return callable
    */
    protected function resolveSortingCallback() 
    {
        $callback = $this->getOption('sorting-callback');
        
        if(!empty($callback) && is_callable($callback))
        {
            return $callback;
        }
        
        return function(DBHelper_BaseRecord $a, DBHelper_BaseRecord $b)
        {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        };
    }
}
