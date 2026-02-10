<?php
/**
 * File containing the {@link UI_MarkupEditorInfo} class.
 * @package Application
 * @subpackage MarkupEditor
 * @see UI_MarkupEditorInfo
 */

declare(strict_types=1);

/**
 * Information container for a markup editor type: allows
 * retrieving a human-readable label and its ID.
 * 
 * Additionally, it allows selecting a markup editor as
 * the default for the whole application.
 *
 * @package Application
 * @subpackage MarkupEditor
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 * 
 * @see UI::getMarkupEditors()
 */
class UI_MarkupEditorInfo
{
    public const string SETTING_NAME_MARKUP_EDITOR_ID = 'MarkupEditorID';

    /**
    * @var string
    */
    private string $id;
    
   /**
    * @var string
    */
    private string $label;
    
    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
   /**
    * Selects this markup editor as the default for the
    * whole application.
    * 
    * @return UI_MarkupEditorInfo
    */
    public function selectAsDefault() : UI_MarkupEditorInfo
    {
        Application_Driver::createSettings()->set(self::SETTING_NAME_MARKUP_EDITOR_ID, $this->getID());
        return $this;
    }
}
