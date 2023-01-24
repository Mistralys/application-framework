<?php
/**
 * File containing the {@link UI_Themes} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Themes
 */

/**
 * Theme manager: manages the available themes and provides a way
 * to access the selected theme details.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Themes
{
    public const ERROR_THEME_CLASS_FILE_NOT_FOUND = 28401;
    public const ERROR_THEME_CLASS_NOT_FOUND = 28402;

    public const OPTION_SHOW_USER_NAME = 'theme_show_user_name';

    protected UI $ui;
    protected string $defaultTheme = 'default';
    protected ?UI_Themes_Theme $theme = null;
    protected ?string $themeID = null;

    /**
    * The paths to the framework templates and the app templates.
    * @var string[]
    */
    protected array $paths;

    public function __construct(UI $ui)
    {
        $this->ui = $ui;
        $this->paths = array(
            APP_ROOT.'/themes',
            APP_INSTALL_FOLDER.'/themes'
        );
    }
    
   /**
    * @return UI
    */
    public function getUI() : UI
    {
        return $this->ui;
    }
   
   /**
    * Retrieves the ID of the currently selected theme.
    * @return string
    */
    public function getThemeID() : string
    {
        if(!isset($this->themeID)) 
        {
            $this->themeID = $this->defaultTheme;
            
            if(defined('APP_THEME') && $this->themeIDExists(APP_THEME)) {
                $this->themeID = APP_THEME;
            }
        }
        
        return $this->themeID;
    }

   /**
    * Retrieves the current theme.
    * 
    * @return UI_Themes_Theme
    * @throws Application_Exception
    * 
    * @see UI_Themes::ERROR_THEME_CLASS_FILE_NOT_FOUND
    * @see UI_Themes::ERROR_THEME_CLASS_NOT_FOUND
    */   
    public function getTheme() : UI_Themes_Theme
    {
        if(isset($this->theme)) 
        {
            return $this->theme;
        }
        
        // we always look in the app's theme folder first
        // to allow overloading the framework version.
        
        $id = $this->getThemeID();
        $path = null;
        
        foreach($this->paths as $folder) 
        {
            $file = $folder.'/'.$id.'/theme.php';
            $real = realpath($file);
            if($real) {
                $path = $real;
            }
        }
        
        if($path === null)
        {
            throw new Application_Exception(
                'Theme class file not found',
                sprintf(
                    'The [theme.php] file could not be found for the [%s] theme in any of the theme folders under [%s].',
                    $id,
                    implode(', ', $this->paths)
                ),
                self::ERROR_THEME_CLASS_FILE_NOT_FOUND
            );
        }
        
        $class = 'Theme_'.str_replace('-', '_', $id);
        require_once $path;
        
        if(!class_exists($class)) 
        {
            throw new Application_Exception(
                'Theme class not found',
                sprintf(
                    'Successfully loaded the [theme.php] for theme [%s], but the expected class [%s] is not present.',
                    $id,
                    $class
                ),
                self::ERROR_THEME_CLASS_NOT_FOUND
            );
        }
        
        $this->theme = new $class($this, $id);
        
        return $this->theme;
    }
    
   /**
    * Checks whether the theme exists in the file system.
    * 
    * @param string $id
    * @return boolean
    */
    public function themeIDExists(string $id) : bool
    {
        foreach($this->paths as $path) {
            $path = realpath($path.'/'.$id);
            if($path && is_dir($path)) {
                return true; 
            }
        }
        
        return false;
    }
}
