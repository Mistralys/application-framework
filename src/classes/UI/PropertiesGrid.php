<?php

use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;

class UI_PropertiesGrid extends UI_Renderable implements Interface_Optionable
{
    use Traits_Optionable;

    const ERROR_ONLY_NUMERIC_VALUES_ALLOWED = 599502;
    
   /**
    * @var string
    */
    protected $id;

   /**
    * @var array
    */
    protected $properties = array();

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
            'label-width-percent' => 20
        );
    }
    
   /**
    * Adds a new property to the grid, and returns the new property instance.
    *
    * @param string|number|UI_Renderable_Interface $label
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_PropertiesGrid_Property_Regular
    */
    public function add($label, $text) : UI_PropertiesGrid_Property_Regular
    {
        return ensureType(
            UI_PropertiesGrid_Property_Regular::class,
            $this->addProperty(new UI_PropertiesGrid_Property_Regular(
                $this, 
                toString($label), 
                toString($text)
            ))
        );
    }
    
   /**
    * Adds a property without label, with all cells merged to be
    * able to use the full width of the table.
    * 
    * @param string|number|UI_Renderable_Interface $content
    * @return UI_PropertiesGrid_Property_Merged
    */
    public function addMerged($content) : UI_PropertiesGrid_Property_Merged
    {
        return ensureType(
            UI_PropertiesGrid_Property_Merged::class,
            $this->addProperty(new UI_PropertiesGrid_Property_Merged(
                $this,
                toString($content)
            ))
        );
    }
    
    public function addDate($label, ?DateTime $date=null) : UI_PropertiesGrid_Property_DateTime
    {
        return ensureType(
            UI_PropertiesGrid_Property_DateTime::class,
            $this->addProperty(new UI_PropertiesGrid_Property_DateTime(
                $this,
                toString($label),
                $date
            ))
        );
    }

   /**
    * Adds a property for an amount of something.
    * The empty text is added automatically.
    * 
    * @param string $label
    * @param number $amount
    * @return UI_PropertiesGrid_Property_Amount
    */
    public function addAmount($label, $amount) : UI_PropertiesGrid_Property_Amount
    {
        return ensureType(
            UI_PropertiesGrid_Property_Amount::class,
            $this->addProperty(new UI_PropertiesGrid_Property_Amount(
                $this, 
                toString($label), 
                $amount
            ))
        );
    }
    
   /**
    * Adds an amount of bytes, which are converted to a readable human format.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @param int $bytes
    * @return UI_PropertiesGrid_Property_ByteSize
    */
    public function addByteSize($label, int $bytes) : UI_PropertiesGrid_Property_ByteSize
    {
        return ensureType(
            UI_PropertiesGrid_Property_ByteSize::class,
            $this->addProperty(new UI_PropertiesGrid_Property_ByteSize(
                $this, 
                toString($label), 
                $bytes
            ))
        );
    }
    
    protected function addProperty(UI_PropertiesGrid_Property $property) : UI_PropertiesGrid_Property
    {
        $this->properties[] = $property;
        
        return $property;
    }
    
   /**
    * Adds a boolean value, which is visually styled.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @param bool $bool
    * @return UI_PropertiesGrid_Property_Boolean
    */
    public function addBoolean($label, bool $bool) : UI_PropertiesGrid_Property_Boolean
    {
        return ensureType(
            UI_PropertiesGrid_Property_Boolean::class,
            $this->addProperty(new UI_PropertiesGrid_Property_Boolean(
                $this, 
                toString($label), 
                $bool
            ))
        );
    }
    
   /**
    * Adds a header to divide sets of properties.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @return UI_PropertiesGrid_Property_Header
    */
    public function addHeader($label) : UI_PropertiesGrid_Property_Header
    {
        return ensureType(
            UI_PropertiesGrid_Property_Header::class,
            $this->addProperty(new UI_PropertiesGrid_Property_Header(
                $this, 
                toString($label)
            ))
        );
    }

    /**
     * Adds all relevant revision information for revisionable items.
     *
     * @param Application_Revisionable $revisionable
     * @param string $changelogURL Optional URL to the changelog; Adds a button to view the changelog.
     * @return UI_PropertiesGrid
     */
    public function injectRevisionDetails(Application_Revisionable $revisionable, $changelogURL = null)
    {
        $user = Application::getUser();

        $this->addHeader(t('Current revision'));

        $revisionable->selectLatestRevision();
        
        $rev = $this->add(
            t('Revision'),
            '<b>' . $revisionable->getPrettyRevision() . '</b> - ' .
            t(
                'Added on %1$s (%2$s).',
                AppUtils\ConvertHelper::date2listLabel($revisionable->getRevisionDate()),
                AppUtils\ConvertHelper::duration2string($revisionable->getRevisionDate())
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
                $revisionable->selectRevision($rev['revisionable_revision']);
                $text =
                    '<span class="text-success">' .
                    '<b>' . $revisionable->getPrettyRevision() . '</b> - ' .
                    t(
                        'Published on %1$s (%2$s).',
                        AppUtils\ConvertHelper::date2listLabel($revisionable->getRevisionDate()),
                        AppUtils\ConvertHelper::duration2string($revisionable->getRevisionDate())
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

            // restore the revision so we do not inadvertently work with older revisions
            $revisionable->selectLatestRevision();
        }

        $this->addHeader(t('Created by'));

        $revisionable->selectFirstRevision();

        $this->add(t('Author'), $revisionable->getOwnerName());
        $this->add(
            t('Date'),
            AppUtils\ConvertHelper::date2listLabel($revisionable->getRevisionDate()) . ' ' .
            '(' . AppUtils\ConvertHelper::duration2string($revisionable->getRevisionDate()) . ')'
        );
        
        $revisionable->selectLatestRevision();

        return $this;
    }
    
   /**
    * Returns the HTML markup for the grid.
    * @return string
    */
    protected function _render()
    {
        $html =
            '<a id="p"></a>' .
            '<table class="table table-condensed table-vertical properties-grid">' .
            '<tbody>';
        foreach ($this->properties as $property) {
            $html .= $property->render();
        }
        $html .=
            '</tbody>' .
            '</table>';

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
     * percentage, which is used for the labels column.
     *
     * @return number
     */
    public function getLabelWidth()
    {
        return $this->getOption('label-width-percent');
    }

    /**
     * Sets the percentual width of the labels column.
     *
     * @param float $percent
     * @throws Application_Exception
     * @return UI_PropertiesGrid
     */
    public function setLabelWidth($percent)
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

        return $this->setOption('label-width-percent', $percent);
    }

   /**
    * @var UI_Page_Section
    */
    protected $section;

   /**
    * Configures the grid to display in a content section.
    * 
    * @param string $title
    * @return UI_PropertiesGrid
    * @see collapse()
    */
    public function makeSection($title=null)
    {
        $this->section = Application_Driver::getInstance()->getPage()->createSection();
        
        if(!empty($title)) {
            $this->section->setTitle($title);
        }
        
        return $this;
    }

   /**
    * If the properties grid has been set to render as
    * a section using {@link makeSection()}, this returns
    * the section instance for further configuration.
    * 
    * @return UI_Page_Section|NULL
    */
    public function getSection()
    {
        return $this->section;
    }

    public function __toString()
    {
        return $this->render();
    }
    
    protected $collapsed;
    
   /**
    * Collapses or uncollapses the grid. Has no effect
    * unless the grid is configured as a content section.
    * 
    * @param boolean $collapse
    * @return UI_PropertiesGrid
    * @see makeSection()
    */
    public function collapse($collapse=true)
    {
        $this->collapsed = true;
        return $this;
    }
}