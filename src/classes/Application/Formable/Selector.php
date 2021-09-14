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
abstract class Application_Formable_Selector implements Interface_Optionable
{
    use Traits_Optionable;

    const DEFAULT_CATEGORY_NAME = '__default';
    const OPTION_MAX_SIZE = 'max-size';
    const OPTION_MAX_HEIGHT = 'max-height';
    const OPTION_IS_MULTISELECT = 'multiselect';
    const OPTION_IS_SELECT_ALL_ENABLED = 'select-all';
    const OPTION_IS_SORTING_ENABLED = 'sorting';
    const OPTION_SORTING_CALLBACK = 'sorting-callback';
    const OPTION_IS_PLEASE_SELECT_ENABLED = 'please-select';
    const OPTION_PLEASE_SELECT_LABEL = 'please-select-label';
    const OPTION_IS_REQUIRED = 'required';
    const  OPTION_IS_MULTIPLE = 'multiple';
    const OPTION_EMPTY_MESSAGE = 'empty-message';
    const OPTION_COMMENTS = 'comment';
    const OPTION_LABEL = 'label';
    const OPTION_NAME = 'name';

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

    /**
     * @var Application_Formable_RecordSelector_Entry[]
     */
    protected $entries = array();

    /**
     * @var bool
     */
    private $loaded = false;

    public function __construct(Application_Interfaces_Formable $formable)
    {
        $this->formable = $formable;
        $this->ui = $formable->getUI();
    }

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_MAX_SIZE => 12,
            self::OPTION_MAX_HEIGHT => 300,
            self::OPTION_IS_MULTISELECT => false,
            self::OPTION_IS_SELECT_ALL_ENABLED => false,
            self::OPTION_IS_SORTING_ENABLED => false,
            self::OPTION_SORTING_CALLBACK => null,
            self::OPTION_IS_PLEASE_SELECT_ENABLED => true,
            self::OPTION_PLEASE_SELECT_LABEL => t('Please select...'),
            self::OPTION_IS_REQUIRED => false,
            self::OPTION_IS_MULTIPLE => false,
            self::OPTION_EMPTY_MESSAGE => '',
            self::OPTION_COMMENTS => '',
            self::OPTION_LABEL => '',
            self::OPTION_NAME => ''
        );
    }

    abstract protected function configureEntry(Application_Formable_RecordSelector_Entry $entry) : void;

    abstract protected function getDefaultName() : string;

    abstract protected function getDefaultLabel() : string;

    abstract protected function _loadEntries() : void;

    protected function loadEntries() : void
    {
        if($this->loaded === true)
        {
            return;
        }

        $this->loaded = true;

        $this->_loadEntries();
    }

    /**
     * Sets the name of the form element.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->setOption(self::OPTION_NAME, $name);
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
        $this->setOption(self::OPTION_IS_PLEASE_SELECT_ENABLED, $enabled);
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
        $this->setOption(self::OPTION_IS_SELECT_ALL_ENABLED, $enabled);
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
        $this->setOption(self::OPTION_PLEASE_SELECT_LABEL, $label);
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
            $this->setOption(self::OPTION_MAX_SIZE, $size);
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
        $this->setOption(self::OPTION_IS_MULTISELECT, true);
        return $this;
    }

    /**
     * Allows selecting several entries.
     *
     * @return $this
     */
    public function makeMultiple()
    {
        $this->setOption(self:: OPTION_IS_MULTIPLE, true);
        return $this;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function makeRequired(bool $required=true)
    {
        $this->setOption(self::OPTION_IS_REQUIRED, $required);
        return $this;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->setOption(self::OPTION_LABEL, $label);
        return $this;
    }

    protected function getName() : string
    {
        $name = $this->getStringOption(self::OPTION_NAME);

        if(!empty($name))
        {
            return $name;
        }

        return $this->getDefaultName();
    }

    protected function getLabel() : string
    {
        $label = $this->getStringOption(self::OPTION_LABEL);

        if(!empty($label))
        {
            return $label;
        }

        return $this->getDefaultLabel();
    }

    /**
     * @param string|number|UI_Renderable_Interface $comment
     * @return $this
     * @throws UI_Exception
     */
    public function setComment($comment)
    {
        $this->setOption(self::OPTION_COMMENTS, toString($comment));
        return $this;
    }

    protected function getComment() : string
    {
        return $this->getStringOption(self::OPTION_COMMENTS);
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setEmptyMessage(string $message)
    {
        $this->setOption(self::OPTION_EMPTY_MESSAGE, $message);
        return $this;
    }

    private function getEmptyMessage() : string
    {
        $message = $this->getStringOption(self::OPTION_EMPTY_MESSAGE);

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
        $this->loadEntries();

        if($this->isEmpty())
        {
            $this->element = $this->injectEmpty();
        }
        else if($this->getBoolOption(self::OPTION_IS_MULTISELECT))
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
        return $this->countEntries() <= 0;
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
        return $this->getBoolOption(self::OPTION_IS_REQUIRED);
    }

    protected function configureElement(HTML_QuickForm2_Element_Select $el) : void
    {
        $el->setComment($this->getComment());
        $el->setRuntimeProperty('__selector', $this);

        if($this->isRequired())
        {
            $this->formable->makeRequired($el);
        }

        if($this->getBoolOption(self::OPTION_IS_PLEASE_SELECT_ENABLED) && !$this->getBoolOption(self:: OPTION_IS_MULTIPLE))
        {
            $el->addOption($this->getStringOption(self::OPTION_PLEASE_SELECT_LABEL), '');
        }

        if($this->getBoolOption(self:: OPTION_IS_MULTIPLE))
        {
            $el->setAttribute(self:: OPTION_IS_MULTIPLE, self:: OPTION_IS_MULTIPLE);
            $el->setAttribute('size', (string)$this->resolveSize());
        }

        $this->addEntries($el);
    }

    protected function addEntries(HTML_QuickForm2_Element_Select $el) : void
    {
        $entries = $this->getEntriesForSelect();

        foreach($entries as $category => $categoryEntries)
        {
            $container = $el;

            if($category !== self::DEFAULT_CATEGORY_NAME)
            {
                $container = $el->addOptgroup($category);
            }

            foreach($categoryEntries as $entry)
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
            $this->configureEntry($record);

            $category = $record->getCategory();
            if(empty($category))
            {
                $category = self::DEFAULT_CATEGORY_NAME;
            }

            if(!isset($result[$category]))
            {
                $result[$category] = array();
            }

            $result[$category][] = $record;
        }

        return $result;
    }

    protected function resolveSize() : int
    {
        return min($this->countEntries(), $this->getMaxSize());
    }

    protected function countEntries() : int
    {
        $this->loadEntries();

        return count($this->entries);
    }

    public function getMaxSize() : int
    {
        return $this->getIntOption(self::OPTION_MAX_SIZE);
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

        if($this->getBoolOption(self::OPTION_IS_SELECT_ALL_ENABLED))
        {
            $el->enableSelectAll();
        }

        $el->enableFiltering();

        $el->setMaxHeight($this->getIntOption(self::OPTION_MAX_HEIGHT));

        return $el;
    }

    protected function registerEntry(string $id, string $label, ?DBHelper_BaseRecord $record=null) : Application_Formable_RecordSelector_Entry
    {
        $entry = new Application_Formable_RecordSelector_Entry($id, $label);

        if($record !== null)
        {
            $entry->setRecord($record);
        }

        $this->entries[] = $entry;

        return $entry;
    }

    /**
     * Retrieves the entries that have been added.
     *
     * @return Application_Formable_RecordSelector_Entry[]
     */
    protected function getEntries() : array
    {
        $this->loadEntries();

        return $this->sortItems($this->entries);
    }

    /**
     * @param Application_Formable_RecordSelector_Entry[] $items
     * @return Application_Formable_RecordSelector_Entry[]
     */
    protected function sortItems(array $items) : array
    {
        if($this->getBoolOption(self::OPTION_IS_SORTING_ENABLED))
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
        $this->setOption(self::OPTION_IS_SORTING_ENABLED, $enable);
        return $this;
    }

    /**
     * Sets the sorting callback function to use to sort the
     * items in the selector.
     *
     * @param callable $callback
     * @return $this
     */
    public function setSortingCallback(callable $callback)
    {
        $this->setOption(self::OPTION_SORTING_CALLBACK, $callback);
        return $this;
    }

    /**
     * @return callable
     */
    protected function resolveSortingCallback()
    {
        $callback = $this->getOption(self::OPTION_SORTING_CALLBACK);

        if(!empty($callback) && is_callable($callback))
        {
            return $callback;
        }

        return function(Application_Formable_RecordSelector_Entry $a, Application_Formable_RecordSelector_Entry $b)
        {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        };
    }
}
