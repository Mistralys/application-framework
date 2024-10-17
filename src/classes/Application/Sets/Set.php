<?php
/**
 * File containing the {@link Application_Sets_Set} class.
 * 
 * @package Application
 * @subpackage Appsets
 * @see Application_Sets_Set
 */

use AppUtils\ConvertHelper;

/**
 * Container for a single application set. Provides an API
 * for accessing set information and manipulating it. Use the
 * sets collection's {@link Application_Sets::getByID()} method
 * to retrieve a specific set.
 * 
 * @package Application
 * @subpackage Appsets
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Sets_Set
{
    public const ERROR_FORMABLE_NOT_VALID = 12801;
    
    public const ERROR_INVALID_DEFAULT_AREA = 12802;
    public const KEY_DEFAULT_AREA = 'defaultArea';
    public const KEY_ID = 'id';

    public const SETTING_ID = 'id';
    public const KEY_ENABLED = 'enabled';

    /**
    * @var string
    */
    protected $id;
    
   /**
    * @var Application_Admin_Area
    */
    protected $defaultArea;
    
   /**
    * @param string $id
    * @param Application_Admin_Area $defaultArea
    */
    public function __construct($id, Application_Admin_Area $defaultArea)
    {
        $this->id = $id;
        $this->defaultArea = $defaultArea;
    }
    
   /**
    * The ID (alias) of the set.
    * @return string
    */
    public function getID()
    {
        return $this->id;
    }
    
   /**
    * The default area that should be opened when this set is active.
    * @return Application_Admin_Area
    */
    public function getDefaultArea()
    {
        return $this->defaultArea;
    }
    
    public static function createSettingsForm(Application_Formable $formable, Application_Sets_Set $set = null)
    {
        $driver = Application_Driver::getInstance();
        $areas = $driver->getAdminAreaObjects(true);
        $formName = 'appsets';
        
        $defaultValues = array();
        if(!$set) {
            $formName = 'appsets-create';
            foreach($areas as $area) {
                $defaultValues['area_'.$area->getID()] = 'true';
            }
        } else {
            $defaultValues[self::SETTING_ID] = $set->getID();
            $defaultValues['default_area'] = $set->getDefaultArea()->getID();
            foreach($areas as $area) {
                $defaultValues['area_'.$area->getID()] = ConvertHelper::bool2string($set->isAreaEnabled($area));
            }
        }
        
        $formable->createFormableForm($formName, $defaultValues);

        if($set) {
            $formable->addHiddenVar('set_id', $set->getID());
        }
        
        $formable->addElementHeader(t('Application set settings'), null, null, false);
        
        $id = $formable->addElementText(self::SETTING_ID, t('ID'));
        $id->addClass('input-xlarge');
        $id->setComment(t('The ID of the application set, which is used to reference it in the code.'));
        $formable->addRuleAlias($id);
        $formable->makeRequired($id);
        $formable->addRuleCallback($id, array('Application_Sets_Set', 'callback_validateID'), t('An application set with this ID already exists.'), array($set));
        
        $def = $formable->addElementSelect('default_area', t('Default admin area'));
        $def->setComment(t('The UI will start with this administration area by default.'));
        $formable->addRuleCallback(
            $def, 
            array('Application_Sets_Set', 'callback_validateDefaultArea'), 
            t('Area must be enabled below.'),
            array($formable)
        );
        
        foreach($areas as $area) {
            $def->addOption($area->getTitle(), $area->getID());
        }

        $formable->addElementHeader(t('Enabled administration areas'), null, null, false);
        
        foreach($areas as $area) 
        {
            // core areas cannot be disabled
            if($area->isCore()) {
                continue;
            }
        
            $el = $formable->addElementSwitch('area_'.$area->getID(), $area->getTitle());
        
            $areas = $area->getDependentAreas();
            if(empty($areas)) {
                $el->setComment(t('Has no dependencies.'));
            } else {
                $labels = array();
                foreach($areas as $darea) {
                    $labels[] = $darea->getTitle();
                }
                $el->setComment(t('Dependencies:').' '.implode(', ', $labels));
                $formable->addRuleCallback(
                    $el,
                    array('Application_Sets_Set', 'callback_validateEnabled'),
                    t('Dependencies must be enabled.'),
                    array($formable, $el, $areas)
                );
            }
        }
        
        $formable->setDefaultElement(self::SETTING_ID);
        
    }

   /**
    * @param string $value
    * @param HTML_QuickForm2_Element_Switch $el
    * @param Application_Admin_Area[] $areas Dependencies
    */
    public static function callback_validateEnabled($value, Application_Formable $formable, HTML_QuickForm2_Element_Switch $el, $areas)
    {
        if(!AppUtils\ConvertHelper::string2bool($value)) {
            return true;
        }
        
        foreach($areas as $area) 
        {
            if($area->isCore()) 
            {
                return true;
            }
            
            $el = self::getAreaElement($area, $formable);
            
            if($el === null || !$el->isChecked()) 
            {
                return false;
            }
        }
        
        return true;
    }
    
    private static function getAreaElement(Application_Admin_Area $area, Application_Formable $formable) : ?HTML_QuickForm2_Element_Switch
    {
        $el = $formable->getElementByName('area_'.$area->getID());
        
        if($el instanceof HTML_QuickForm2_Element_Switch)
        {
            return $el;
        }
        
        return null;
    }
    
    public static function callback_validateDefaultArea($areaID, Application_Formable $formable)
    {
        $driver = Application_Driver::getInstance();
        $area = $driver->createArea($areaID);
        if($area->isCore()) {
            return true;
        }
        
        $el = self::getAreaElement($area, $formable);
        
        if($el === null || !$el->isChecked()) 
        {
            return false;
        }
        
        return true;
    }
    
   /**
    * Creates a new application set using the form configured
    * in the specified formable. Must be a form created with
    * the {@link createSettingsForm} method.
    * 
    * @param Application_Formable $formable
    * @return Application_Sets_Set
    */
    public static function createFromFormable(Application_Formable $formable)
    {
        if(!$formable->isFormValid()) {
            throw new Application_Exception(
                'Formable must be valid',
                'The specified formable was not valid. Please validate it before you call createFromFormable.',
                self::ERROR_FORMABLE_NOT_VALID
            );
        }
        
        $values = $formable->getFormValues();
        
        $driver = Application_Driver::getInstance();
        
        // fetch enabled areas from the form result
        $enabled = array();
        $areas = $driver->getAdminAreaObjects(false);
        foreach($areas as $area) {
            $value = $values['area_'.$area->getID()];
            if(ConvertHelper::string2bool($value)) {
                $enabled[] = $area;
            }
        }
        
        $sets = Application_Sets::getInstance();
        
        return $sets->createNew(
            $values[self::SETTING_ID],
            $driver->createArea($values['default_area']), 
            $enabled
        );
    }
 
   /**
    * @var Application_Admin_Area[]
    */
    protected $enabled = array();
    
   /**
    * Enables the specified area for the set.
    * 
    * @param Application_Admin_Area $area
    * @return Application_Sets_Set
    */
    public function enableArea(Application_Admin_Area $area)
    {
        $id = $area->getID();
        
        if(!isset($this->enabled[$id])) {
            $this->enabled[$id] = $area;
        }
        
        return $this;
    }
    
   /**
    * Serializes the set to an array representation.
    * @return array
    * @see fromArray()
    */
    public function toArray()
    {
        return array(
            self::KEY_ID => $this->getID(),
            self::KEY_DEFAULT_AREA => $this->defaultArea->getID(),
            self::KEY_ENABLED => array_keys($this->enabled)
        );
    }
    
   /**
    * Creates a set instance from a previously created set array.
    * @param array<string,mixed> $data
    * @return Application_Sets_Set
    * @see toArray()
    */
    public static function fromArray(array $data) : Application_Sets_Set
    {
        $driver = Application_Driver::getInstance();

        if(!$driver->areaExists($data[self::KEY_DEFAULT_AREA])) {
            throw new Application_Exception(
                'Invalid default area in appset.',
                sprintf(
                    'The default area [%s] in appset [%s] does not exist. Available areas are [%s].',
                    $data[self::KEY_DEFAULT_AREA],
                    $data[self::KEY_ID],
                    implode(', ', array_keys($driver->getAdminAreas()))
                ),
                self::ERROR_INVALID_DEFAULT_AREA
            );
        }
        
        $set = new Application_Sets_Set($data[self::KEY_ID], $driver->createArea($data[self::KEY_DEFAULT_AREA]));
        
        foreach($data[self::KEY_ENABLED] as $areaID) {
            if($driver->areaExists($areaID)) {
                $set->enableArea($driver->createArea($areaID));
            }
        }
        
        return $set;
    }
    
   /**
    * Retrieves all areas enabled for this set.
    * @param bool $includeCore
    * @return Application_Admin_Area[]
    */
    public function getEnabledAreas(bool $includeCore=true)
    {
        $core = array();
        
        if($includeCore) {
            $areas = Application_Driver::getInstance()->getAdminAreaObjects();
            foreach($areas as $area) {
                if($area->isCore()) {
                    $core[] = $area;
                }
            }
        }
        
        return array_merge($this->enabled, $core);
    }
    
   /**
    * Retrieves the human readable names (titles) of all
    * areas that are currently enabled.
    * 
    * @param bool $includeCore Whether to include core areas
    * @return string[]
    */
    public function getEnabledAreaNames(bool $includeCore=true)
    {
        $enabled = $this->getEnabledAreas($includeCore);
        $result = array();
        foreach($enabled as $area) {
            $result[] = $area->getTitle();
        }
        
        return $result;
    }
    
   /**
    * Retrieves the IDs of all areas that are currently enabled.
    * 
    * @param bool $includeCore Whether to include core areas
    * @return string[]
    */
    public function getEnabledAreaIDs(bool $includeCore=true)
    {
        $enabled = $this->getEnabledAreas($includeCore);
        $result = array();
        foreach($enabled as $area) {
            $result[] = $area->getID();
        } 
        
        return $result;
    }
    
   /**
    * Retrieves the URL to the set's edit screen.
    * @param array $params
    * @return string
    */
    public function getAdminEditURL($params=array())
    {
        $params['submode'] = 'edit';
        return $this->getAdminURL($params);
    }
    
   /**
    * Retrieves the URL to the set's delete screen.
    * @param array $params
    * @return string
    */
    public function getAdminDeleteURL($params=array())
    {
        $params['submode'] = 'delete';
        return $this->getAdminURL($params);
    }
    
    protected function getAdminURL($params=array())
    {
        $params['page'] = 'devel';
        $params['mode'] = 'appsets';
        $params['set_id'] = $this->getID();
        
        $request = Application_Driver::getInstance()->getRequest();
        return $request->buildURL($params);
    }
    
   /**
    * Checks whether the specified area is enabled for this 
    * application set. Core areas are always enabled.
    * 
    * @param Application_Admin_Area $area
    * @return boolean
    */
    public function isAreaEnabled(Application_Admin_Area $area)
    {
        $enabled = $this->getEnabledAreas();
        return in_array($area, $enabled);
    }
    
   /**
    * Updates the set from form values from a set settings form.
    * Note that the sets have to be saved after this operation.
    * 
    * @param array $formValues
    */
    public function updateFromForm($formValues)
    {
        $sets = Application_Sets::getInstance();
        $driver = Application_Driver::getInstance();
        
        if($formValues[self::SETTING_ID] !== $this->id) {
            $sets->handle_renameSet($this, $formValues[self::SETTING_ID]);
            $this->id = $formValues[self::SETTING_ID];
        }
        
        $this->defaultArea = $driver->createArea($formValues['default_area']);
        
        $this->enabled = array();
        
        $areas = $driver->getAdminAreaObjects(false);
        foreach($areas as $area) {
            if(ConvertHelper::string2bool($formValues['area_'.$area->getID()])) {
                $this->enableArea($area);
            }
        }
    }
    
   /**
    * @param string $setID
    * @param Application_Sets_Set|NULL $excludeSet
    * @return boolean
    */
    public static function callback_validateID($setID, Application_Sets_Set $excludeSet=null)
    {
        if($excludeSet && $setID == $excludeSet->getID()) {
            return true;
        }
        
        return !Application_Sets::getInstance()->idExists($setID);
    }
    
   /**
    * Enables a collection of areas at once.
    * @param Application_Admin_Area[] $areas
    * @return Application_Sets_Set
    */
    public function enableAreas($areas)
    {
        foreach($areas as $area) {
            $this->enableArea($area);
        }
        
        return $this;
    }
    
   /**
    * Whether this is the currently active application set.
    * @return boolean
    */
    public function isActive()
    {
        if(defined('APP_APPSET') && APP_APPSET == $this->id) {
            return true;
        }
        
        return false;
    }
}