<?php
/**
 * @package UserInterface
 * @subpackage Helpers
 * @see UI_PropertiesGrid
 */

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OutputBuffering;
use AppUtils\OutputBuffering_Exception;
use AppUtils\Traits\OptionableTrait;
use UI\PropertiesGrid\Property\MarkdownGridProperty;

/**
 * Specialized table view used to display item
 * properties, with the property names on the
 * right side.
 *
 * @package UserInterface
 * @subpackage Helpers
 */
class UI_PropertiesGrid extends UI_Renderable implements OptionableInterface, UI_Interfaces_Conditional
{
    use OptionableTrait;
    use UI_Traits_Conditional;

    public const ERROR_ONLY_NUMERIC_VALUES_ALLOWED = 599502;
    public const OPTION_LABEL_WIDTH = 'label-width-percent';
    public const DEFAULT_LABEL_WIDTH = 20; // percent

    protected string $id;

   /**
    * @var UI_PropertiesGrid_Property[]
    */
    protected array $properties = array();

    public function __construct(UI_Page $page, string $id = '')
    {
        parent::__construct($page);
        
        if(empty($id)) 
        {
            $id = nextJSID();
        }

        $this->id = $id;
    }

    public function getID() : string
    {
        return $this->id;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_LABEL_WIDTH => self::DEFAULT_LABEL_WIDTH
        );
    }

    //region: Property types

    /**
     * Adds a new property to the grid, and returns the new property instance.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @param string|number|UI_Renderable_Interface $text
     * @return UI_PropertiesGrid_Property_Regular
     * @throws UI_Exception
     */
    public function add($label, $text) : UI_PropertiesGrid_Property_Regular
    {
        $property = new UI_PropertiesGrid_Property_Regular(
            $this,
            toString($label),
            toString($text)
        );

        $this->addProperty($property);

        return $property;
    }

    /**
     * Adds a property without label, with all cells merged to be
     * able to use the full width of the table.
     *
     * @param string|number|UI_Renderable_Interface $content
     * @return UI_PropertiesGrid_Property_Merged
     * @throws UI_Exception
     */
    public function addMerged($content) : UI_PropertiesGrid_Property_Merged
    {
        $prop =  new UI_PropertiesGrid_Property_Merged($this, '', $content);
        $this->addProperty($prop);
        return $prop;
    }

    /**
     * @param string|number|UI_Renderable_Interface $message
     * @return UI_PropertiesGrid_Property_Message
     * @throws UI_Exception
     */
    public function addMessage($message) : UI_PropertiesGrid_Property_Message
    {
        $prop = new UI_PropertiesGrid_Property_Message($this, '', toString($message));
        $this->addProperty($prop);
        return $prop;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @param DateTime|null $date
     * @return UI_PropertiesGrid_Property_DateTime
     * @throws UI_Exception
     */
    public function addDate($label, ?DateTime $date=null) : UI_PropertiesGrid_Property_DateTime
    {
        $prop = new UI_PropertiesGrid_Property_DateTime(
            $this,
            toString($label),
            $date
        );

        $this->addProperty($prop);

        return $prop;
    }

    /**
     * Adds a property for an amount of something.
     * The empty text is added automatically.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @param number $amount
     * @return UI_PropertiesGrid_Property_Amount
     * @throws UI_Exception
     */
    public function addAmount($label, $amount) : UI_PropertiesGrid_Property_Amount
    {
        $prop = new UI_PropertiesGrid_Property_Amount(
            $this,
            toString($label),
            $amount
        );

        $this->addProperty($prop);

        return $prop;
    }

    /**
     * Adds a text that will be rendered as Markdown formatted text.
     *
     * @param string|number|UI_Renderable_Interface|NULL $markdownText
     * @return MarkdownGridProperty
     * @throws UI_Exception
     */
    public function addMarkdown($markdownText) : MarkdownGridProperty
    {
        $prop = new MarkdownGridProperty($this, '', toString($markdownText));
        $this->addProperty($prop);
        return $prop;
    }

    /**
     * Adds a number of bytes, which are converted to a readable human format.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @param int $bytes
     * @return UI_PropertiesGrid_Property_ByteSize
     * @throws UI_Exception
     */
    public function addByteSize($label, int $bytes) : UI_PropertiesGrid_Property_ByteSize
    {
        $prop = new UI_PropertiesGrid_Property_ByteSize(
            $this,
            toString($label),
            $bytes
        );

        $this->addProperty($prop);

        return $prop;
    }
    
    /**
     * Adds a boolean value, which is visually styled.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @param bool $bool
     * @return UI_PropertiesGrid_Property_Boolean
     * @throws UI_Exception
     */
    public function addBoolean($label, bool $bool) : UI_PropertiesGrid_Property_Boolean
    {
        $prop = new UI_PropertiesGrid_Property_Boolean(
            $this,
            toString($label),
            $bool
        );

        $this->addProperty($prop);

        return $prop;
    }

    /**
     * Adds a header to divide sets of properties.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return UI_PropertiesGrid_Property_Header
     * @throws UI_Exception
     */
    public function addHeader($label) : UI_PropertiesGrid_Property_Header
    {
        $prop = new UI_PropertiesGrid_Property_Header(
            $this,
            toString($label)
        );

        $this->addProperty($prop);

        return $prop;
    }

    // endregion

    protected function addProperty(UI_PropertiesGrid_Property $property) : UI_PropertiesGrid_Property
    {
        $this->properties[] = $property;

        return $property;
    }

    /**
     * Adds all relevant revision information for revisionable items.
     *
     * @param RevisionableInterface $revisionable
     * @param string|NULL $changelogURL Optional URL to the changelog; Adds a button to view the changelog.
     * @return $this
     *
     * @throws Application_Exception
     * @throws DBHelper_Exception
     * @throws UI_Exception
     * @throws ConvertHelper_Exception
     */
    public function injectRevisionDetails(RevisionableInterface $revisionable, ?string $changelogURL = null) : self
    {
        $user = Application::getUser();

        $this->addHeader(t('Current revision'));

        $revisionable->selectLatestRevision();
        
        $rev = $this->add(
            t('Revision'),
            '<b>' . $revisionable->getPrettyRevision() . '</b> - ' .
            t(
                'Added on %1$s (%2$s).',
                ConvertHelper::date2listLabel($revisionable->getRevisionDate()),
                ConvertHelper::duration2string($revisionable->getRevisionDate())
            )
        );
        if ($user->isDeveloper()) {
            $rev->setComment(t('Internal revision:') . ' ' . $revisionable->getRevision());
        }

        $state = $this->add(t('State'), $revisionable->getCurrentPrettyStateLabel());
        if (!empty($changelogURL)) {
            $state->addButton(
                UI::button(t('View changelog'))
                    ->setIcon(UI::icon()->view())
                    ->link($changelogURL)
            );
        }

        $this->add(t('Author'), $revisionable->getOwnerName());
        $this->add(t('Comments'), $revisionable->getRevisionComments())->ifEmpty('<span class="muted">' . t('No comments') . '</span>');

        // if the revisionable supports clearing, find out if it has been
        // published before and when was the last published version.
        $clearable = $revisionable->hasState('cleared');
        if ($clearable) {

            $this->addHeader(t('Last cleared revision'));

            // we use the revisionable's revision criteria to
            // fetch the last cleared revision if any.
            $criteria = $revisionable->getRevisionsFilterCriteria();
            $criteria->selectState('cleared');
            $criteria->orderDescending();
            $criteria->setLimit(1, 0);
            $revs = $criteria->getItems();
            
            $found = false;
            if(!empty($revs)) {
                $found = true;
                $rev = $revs[0];
                $revisionable->selectRevision((int)$rev['revisionable_revision']);
                $text =
                    '<span class="text-success">' .
                    '<b>' . $revisionable->getPrettyRevision() . '</b> - ' .
                    t(
                        'Published on %1$s (%2$s).',
                        ConvertHelper::date2listLabel($revisionable->getRevisionDate()),
                        ConvertHelper::duration2string($revisionable->getRevisionDate())
                    ) .
                    '</span>';
            } else {
                $text = '<span class="text-warning">' . t('None, has never been published.') . '</span>';
            }

            $pubRev = $this->add(t('Revision'), $text);

            if ($found && $user->isDeveloper()) {
                $pubRev->setComment(t('Internal revision:') . ' ' . $revisionable->getRevision());
            }

            if ($found) {
                $this->add(t('Author'), $revisionable->getOwnerName());
                $this->add(t('Comments'), $revisionable->getRevisionComments())->ifEmpty('<span class="muted">' . t('No comments') . '</span>');
            }

            // restore the revision, so we do not inadvertently work with older revisions
            $revisionable->selectLatestRevision();
        }

        $this->addHeader(t('Created by'));

        $revisionable->selectFirstRevision();

        $this->add(t('Author'), $revisionable->getOwnerName());
        $this->add(
            t('Date'),
            ConvertHelper::date2listLabel($revisionable->getRevisionDate()) . ' ' .
            '(' . ConvertHelper::duration2string($revisionable->getRevisionDate()) . ')'
        );
        
        $revisionable->selectLatestRevision();

        return $this;
    }

    /**
     * @return UI_PropertiesGrid_Property[]
     */
    private function resolveProperties() : array
    {
        $result = array();

        foreach ($this->properties as $property) {
            if($property->isValid()) {
                $result[] = $property;
            }
        }

        return $result;
    }

    /**
     * Returns the HTML markup for the grid.
     *
     * @return string
     * @throws OutputBuffering_Exception
     */
    protected function _render() : string
    {
        if(!$this->isValid()) {
            return '';
        }

        $this->ui->addStylesheet('ui-properties-grid.css');

        $properties = $this->resolveProperties();

        if(empty($properties)) {
            return '';
        }

        OutputBuffering::start();

        ?>
        <a id="properties-grid-<?php echo $this->id ?>"></a>
        <table class="table table-condensed table-vertical properties-grid">
            <tbody>
                <?php
                    foreach ($properties as $property)
                    {
                        echo $property->render();
                    }
                ?>
            </tbody>
        </table>
        <?php

        $html = OutputBuffering::get();

        if (isset($this->section)) {
            $this->section->setContent($html);
            
            if(isset($this->collapsed)) {
                $this->section->setCollapsed($this->collapsed);
            }
            
            $html = $this->section->render();
        }

        return $html;
    }

    /**
     * Retrieves the current value of the label width
     * percentage, which is used for the label column.
     *
     * @return int|float
     */
    public function getLabelWidth()
    {
        return $this->getOption(self::OPTION_LABEL_WIDTH);
    }

    /**
     * Sets the percentual width of the label column.
     *
     * @param int|float $percent
     * @throws Application_Exception
     * @return UI_PropertiesGrid
     */
    public function setLabelWidth($percent) : UI_PropertiesGrid
    {
        if (!is_numeric($percent)) {
            throw new Application_Exception(
                'Only numeric values allowed',
                sprintf(
                    'Tried setting the label width to [%s], but only numeric values are allowed.',
                    $percent
                ),
                self::ERROR_ONLY_NUMERIC_VALUES_ALLOWED
            );
        }

        if ($percent < 1) {
            $percent = 1;
        }

        if ($percent > 100) {
            $percent = 100;
        }

        return $this->setOption(self::OPTION_LABEL_WIDTH, $percent);
    }

   /**
    * @var UI_Page_Section|NULL
    */
    protected ?UI_Page_Section $section = null;

   /**
    * Configures the grid to display in a content section.
    * 
    * @param string|number|UI_Renderable_Interface $title
    * @return UI_PropertiesGrid
    * @see collapse()
    */
    public function makeSection($title='') : UI_PropertiesGrid
    {
        $this->section = Application_Driver::getInstance()->getPage()->createSection();
        
        if(!empty($title)) {
            $this->section->setTitle($title);
        }
        
        return $this;
    }

   /**
    * If the property grid has been set to render as
    * a section using {@link makeSection()}, this returns
    * the section instance for further configuration.
    * 
    * @return UI_Page_Section|NULL
    */
    public function getSection() : ?UI_Page_Section
    {
        return $this->section;
    }

    public function __toString() : string
    {
        return $this->render();
    }

    protected ?bool $collapsed = null;
    
   /**
    * Collapses or un-collapses the grid. Has no effect
    * unless the grid is configured as a content section.
    * 
    * @param boolean $collapse
    * @return UI_PropertiesGrid
    * @see makeSection()
    */
    public function collapse(bool $collapse=true) : UI_PropertiesGrid
    {
        $this->collapsed = $collapse;
        return $this;
    }
}