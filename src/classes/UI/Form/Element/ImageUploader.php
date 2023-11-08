<?php
/**
 * File containing the {@see HTML_QuickForm2_Element_ImageUploader} class.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_ImageUploader
 */

use Application\Media\Collection\MediaCollection;
use AppUtils\ImageHelper_Exception;
use AppUtils\ImageHelper;
use AppUtils\OutputBuffering;
use function AppUtils\parseNumber;
use AppUtils\ImageHelper_Size;
use AppUtils\NumberInfo;

/**
 * Element that is used to handle SPIN image uploads: handles an image upload
 * in its own dialog window and processes image transformations directly.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_ImageUploader extends HTML_QuickForm2_Element_Input
{
    public const THUMBNAIL_WIDTH = 75;
    public const THUMBNAIL_HEIGHT = 75;

    protected bool $persistent = true;

    protected array $attributes = array('type' => 'image');

    protected static array $supportedExtensions = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg'
    );

    protected $uploadData = array(
        'name' => '',
        'state' => 'empty',
        'id' => ''
    );
    
   /**
    * @var int|NULL
    */
    protected $width;
    
   /**
    * @var int|NULL
    */
    protected $height;

    protected function initNode() : void
    {
        $this->setRuntimeProperty('comments-callback', array($this, 'getAutoComments'));
    }
    
   /**
    * Retrieves the default, empty value for image uploader elements.
    * This is an array with three keys:
    *
    * - name
    * - state
    * - id
    *
    * @return array{name:string,state:string,id:string}
    */
    public static function getDefaultData() : array
    {
        return array(
            'name' => '',
            'state' => 'empty',
            'id' => ''
        );
    }

    /**
     * Overridden to allow storing the image upload field's
     * array value from the three input elements it is made of.
     *
     * @return $this
     * @throws Application_Exception
     *
     * @see HTML_QuickForm2_Element_Input::setValue()
     */
    public function setValue($value) : self
    {
        if (!is_array($value)) {
            throw new Application_Exception(
                'Invalid value'
            );
        }

        if (!isset($value['name'], $value['state'], $value['id'])) {
            throw new Application_Exception(
                'Key missing'
            );
        }

        $value['name'] = trim($value['name']);
        
        $this->uploadData = $value;

        parent::setValue($value['name']);

        return $this;
    }

    /**
     * Override regular method: image upload fields return an array
     * value with three keys: name, state and id.
     *
     * @see HTML_QuickForm2_Node::getValue()
     */
    public function getValue()
    {
        return $this->uploadData;
    }

    public function __toString()
    {
        $ui = UI::getInstance();
        $ui->addStylesheet('image_uploader.css');
        
        $value = $this->getValue();

        $name = $value['name'];
        $state = $value['state'];
        $id = $value['id'];
        $media = $this->getMediaByValue($value);
        $preview = $this->getThumbnailURL($value);
        $inputName = $this->getAttribute('name');
        $baseID = $this->getAttribute('id');
        if(empty($baseID)) {
            $baseID = 'up' . nextJSID();
        }

        $thumbClass = '';
        if ($state != 'empty') {
            $thumbClass = 'clickable';
        }

        $fileTypeDisplay = 'none';
        $fileType = null;
        if($media !== null) {
            $fileType = $media->getExtension();
            $fileTypeDisplay = 'block';
        }
        
        $this->getComment();

        if($this->frozen) 
        {
            $html = 
            '<div class="imageuploader_preview_container" id="'.$baseID.'">' .
                '<div class="imageuploader_preview">' .
                    '<span class="imageuploader_badge_filetype" id="%1$s_filetype" style="display:'.$fileTypeDisplay.'">'.$fileType.'</span>'.
                    '<img src="' . $preview . '" alt="" class="' . $thumbClass . '"/>' .
                '</div>' .
            '</div>';
            
            return $html;
        }

        OutputBuffering::start();

        ?>
<div class="imageuploader_container" id="{BASE_ID}">
    <table style="width:100%%">
        <tbody>
            <tr>
                <td class="imageuploader_preview_container">
                    <div class="imageuploader_preview">
                        <span class="imageuploader_badge_filetype" id="{BASE_ID}_filetype" style="display:<?php echo $fileTypeDisplay ?>"><?php echo $fileType ?></span>
                        <img id="{BASE_ID}_thumbnail" src="<?php echo $preview ?>" alt="" class="<?php echo $thumbClass ?>"/>
                    </div>
                </td>
                <td>
                    <div class="imageuploader_button">
                        <div class="input-prepend input-append">
                            <button id="{BASE_ID}_browse" class="btn" type="button" title="<?php pt('Browse your computer to select an image to upload.') ?>">
                                <?php echo UI::icon()->upload() ?>
                            </button>
                            <input id="{BASE_ID}_name" type="text" name="{ELEMENT_NAME}[name]" value="<?php echo $name ?>" class="imageuploader_name"/>
                            <button id="{BASE_ID}_delete" class="btn btn-danger" type="button" title="<?php pt('Reset this image: clear the uploaded file and name.') ?>">
                                <?php echo UI::icon()->delete() ?>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="f_{ELEMENT_NAME}" value="yes" data-element-type="ImageUploader" data-id="{BASE_ID}"/>
                    <input id="{BASE_ID}_state" type="hidden" name="{ELEMENT_NAME}[state]" value="<?php echo $state ?>"/>
                    <input id="{BASE_ID}_id" type="hidden" name="{ELEMENT_NAME}[id]" value="<?php echo $id ?>"/>
                    <div id="{BASE_ID}_file" class="imageuploader_filename"></div>
                    <div id="{BASE_ID}_progress" class="imageuploader_progressbar"></div>
                    <div id="{BASE_ID}_statusbar" class="imageuploader_statusbar">
                        <?php echo UI::icon()->information()->makeInformation() ?>
                        <span class="muted">
                            <?php pt('%1$sSelect an image%2$s to upload.', '<a href="javascript:void(0);" id="{BASE_ID}_browse2">', '</a>') ?>
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php

        $vars = array(
            '{BASE_ID}' => $baseID,
            '{ELEMENT_NAME}' => $inputName
        );

        $html = (string)str_replace(
            array_keys($vars),
            array_values($vars),
            OutputBuffering::get()
        );
        
        self::initJavascript();

        $ui = UI::getInstance();
        $ui->addJavascriptOnload(sprintf("ImageUploader.Add('%s')", $baseID));

        return $html;
    }

    protected static $javascriptInitialized = false;

    protected static function initJavascript()
    {
        if (self::$javascriptInitialized) {
            return;
        }

        self::$javascriptInitialized = true;

        $ui = UI::getInstance();

        $ui->addJavascript('plupload.full.min.js');
        $ui->addJavascript('image_uploader.js');
        $ui->addJavascriptHeadVariable('ImageUploader.thumbnailWidth', self::THUMBNAIL_WIDTH);
        $ui->addJavascriptHeadVariable('ImageUploader.thumbnailHeight', self::THUMBNAIL_HEIGHT);
        $ui->addJavascriptHeadVariable('ImageUploader.imageExtensions', self::$supportedExtensions);
        $ui->addJavascriptHeadVariable('ImageUploader.emptyImageURL', $ui->getTheme()->getImageURL('empty-image.png'));
    }

    protected function getThumbnailURL($value)
    {
        $media = $this->getMediaByValue($value);
        if($media !== null) {
            return $media->getThumbnailURL(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        }
        
        return UI::getInstance()->getTheme()->getImageURL('empty-image.png');
    }
    
   /**
    * Retrieves the media instance for the current value of the uploader, if any.
    * @return Application_Media_DocumentInterface|NULL
    */
    public function getMedia()
    {
        return $this->getMediaByValue($this->getValue());
    }
    
   /**
    * Retrieves the media document for the specified uploader value.
    * @param array|NULL $value
    * @return Application_Media_DocumentInterface|NULL
    */
    public static function getMediaByValue(?array $value) : ?Application_Media_DocumentInterface
    {
        if(!is_array($value)) {
            return null;
        }

        switch ($value['state']) {
            case 'new':
                $uploads = Application_Uploads::getInstance();
                return $uploads->getByID($value['id']);

            case MediaCollection::MEDIA_TYPE:
                $media = Application_Media::getInstance();
                return $media->getByID($value['id']);
        }
        
        return null;
    }
    
    public static function isValidMedia($value)
    {
        $media = self::getMediaByValue($value);
        return !is_null($media);
    }

    protected function validate() : bool
    {
        parent::validate();
        
        $value = $this->getValue();

        if($value['state'] === 'empty') {
            foreach($this->rules as $def) {
                if($def[0] instanceof HTML_QuickForm2_Rule_Required) {
                    $this->error = $def[0]->getMessage();
                }
            }
        }
        
        return !isset($this->error);
    }

    /**
     * Checks if the specified image file name is a supported
     * image type by checking its extension. Returns the extension
     * if it is supported, false otherwise.
     *
     * @param string $fileName
     * @return boolean|string
     */
    public static function isSupportedFile($fileName)
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::$supportedExtensions)) {
            return false;
        }

        return $ext;
    }

   /**
    * Upgrades the uploaded media file to a regular media document
    * if a file has been uploaded. Has no effect otherwise, and can
    * safely be called if the media has already been upgraded.
    * 
    * Note: This is called automatically by the form in the 
    * postValidation routine, and does not need to be called manually.
    * 
    * @see UI_Form::postValidation()
    */
    public function upgradeMedia() : void
    {
        $media = $this->getMedia();
        if(!$media) {
            return;
        }
        
        $document = $media->upgrade();
        $this->uploadData['state'] = MediaCollection::MEDIA_TYPE;
        $this->uploadData['id'] = $document->getID();
    }
    
   /**
    * @var HTML_QuickForm2_Rule_Callback|NULL
    */
    protected ?HTML_QuickForm2_Rule_Callback $ruleEvenSized = null;
    
   /**
    * Ensures that the image dimensions are even sized (width and height).
    */
    public function addRuleEvenSized()
    {
        if(!isset($this->ruleEvenSized)) {
            $this->ruleEvenSized = $this->addRuleCallback(
                t('The image dimensions have to be even numbers.'),  
                array($this, 'validate_evenSized')
            );
        }
        
        return $this->ruleEvenSized;
    }
    
   /**
    * @var HTML_QuickForm2_Rule_Callback|NULL
    */
    protected ?HTML_QuickForm2_Rule_Callback $ruleMinSize = null;
    
   /**
    * Adds a rule for a minimum image size, with optionally a recommendation
    * to upload it double that.
    * 
    * @param int $width
    * @param int $height
    * @return HTML_QuickForm2_Rule_Callback
    */
    public function addRuleMinSize($width=0, $height=0)
    {
        if(!isset($this->ruleMinSize)) {
            $this->ruleMinSize = $this->addRuleCallback(
                '', // set in the callback
                array($this, 'validate_minSize')
            );
        }
        
        $this->ruleMinSize->setArguments(array($width, $height));
        
        return $this->ruleMinSize;
    }
    
   /**
    * Adds a recommendation to upload the image in double 
    * resolution. Requires the min size rule to be added.
    */
    public function makeDoubleResolution()
    {
        $this->setRuntimeProperty('double-resolution', true);
    }
    
    public function isDoubleResolution()
    {
        return $this->getRuntimeProperty('double-resolution') === true;
    }
    
    public function hasMinSizeRule()
    {
        return isset($this->ruleMinSize);
    }
    
    public function getAutoComments()
    {
        $parts = array();
        
        if($this->hasMinSizeRule()) 
        {
            $size = $this->ruleMinSize->getArguments();
            
            $width = \AppUtils\parseNumber($size[0]);
            $height = \AppUtils\parseNumber($size[1]);
            $recommendedSize = '';
            
            if($width->isPositive() && $height->isPositive()) 
            {
                $recommendedSize = t('%1$s x %2$s pixels', $width->getNumber() * 2, $height->getNumber() * 2);
                $parts[] = t(
                    'The image must have a minimum size of %1$s x %2$s pixels.', 
                    $width->getNumber(),
                    $height->getNumber()
                );
            }
            else if($width->isPositive())
            {
                $recommendedSize = t('%1$s pixels width', $width->getNumber() * 2);
                $parts[] = t(
                    'The image must have a minimum width of %1$s pixels.',
                    $width->getNumber()
                );
            }
            else if($height->isPositive())
            {
                $recommendedSize = t('%1$s pixels height', $height->getNumber() * 2);
                $parts[] = t(
                    'The image must have a minimum height of %1$s pixels.',
                    $height->getNumber()
                );
            }
            
            if($this->isDoubleResolution() && !empty($recommendedSize)) 
            {
                $parts[] = t(
                    'For optimum results, we recommend doubling the image\'s resolution to %1$s.',
                    $recommendedSize
                );
            }
        }
        
        if($this->hasEvenSizedRule()) 
        {
            $parts[] = t('Both width and height must be even numbers, so the image can be resized cleanly.');
        }
        
        if(!empty($parts)) {
            return implode(' ', $parts);
        }
        
        return null;
    }
    
    protected function getPathByValue($value)
    {
        $media = self::getMediaByValue($value);
        if($media !== null) {
            return $media->getPath();
        }
        
        return null;
    }
    
    protected function getImageHelperByValue($value) : ?ImageHelper
    {
        $path = $this->getPathByValue($value);
        if($path) {
            return ImageHelper::createFromFile($path);
        }
        
        return null;
    }
    
   /**
    * Checks whether the element has an even sized rule.
    * @return boolean
    */ 
    public function hasEvenSizedRule()
    {
        return isset($this->ruleEvenSized);
    }
    
    private function resolveImageSize(AppUtils\ImageHelper $helper, HTML_QuickForm2_Rule_Callback $rule) : ?ImageHelper_Size
    {
        try
        {
            return $helper->getSize();
        }
        catch(ImageHelper_Exception $e)
        {
            
        }
        
        $rule->setMessage((string)sb()
            ->t('Could not recognize the image format.')
            ->t('The image file is possibly corrupted.')
            ->t('Please try opening and saving it to a new file with a graphics program like Photoshop, then upload that new file.')
        );
            
        return null;
    }
    
    public function validate_evenSized($value, HTML_QuickForm2_Rule_Callback $rule) : bool
    {
        $helper = $this->getImageHelperByValue($value);
        if($helper === null) 
        {
            return true;
        }
        
        // Vector images need no even sized check.
        if($helper->isVector()) 
        {
            return true;
        }
        
        $size = $this->resolveImageSize($helper, $rule);
        if($size === null) 
        {
            return false;
        }
        
        $width = parseNumber($size->getWidth());
        $height = parseNumber($size->getHeight());
        
        if($width->isZeroOrEmpty() && $height->isZeroOrEmpty())
        {
            $rule->setMessage(sb()
                ->t('Could not determine the image dimensions.')
                ->t('The image file is possibly corrupted.')
                ->t('Please try opening and saving it to a new file with a graphics program like Photoshop, then upload that new file.')
            );
        }
        
        if($width->isEven() && $height->isEven()) 
        {
            return true;
        }
        
        $rule->setMessage((string)sb()
            ->t('The image does not have even-numbered dimensions.')
            ->t('For clean resizing, the width and height should be even numbers.')
            ->t('Dimensions of the uploaded image:')
            ->add($this->renderDimensionsLabel($width, $height))
        );
    
        return false;
    }
    
    private function renderDimensionsLabel(NumberInfo $width, NumberInfo $height) : UI_StringBuilder
    {
        $dimensions = sb();
        
        if(!$width->isEven())
        {
            $dimensions->danger(sb()->bold($width->getNumber()));
        }
        else
        {
            $dimensions->add($width->getNumber());
        }
        
        $dimensions->add('x');
        
        if(!$height->isEven())
        {
            $dimensions->danger(sb()->bold($height->getNumber()));
        }
        else
        {
            $dimensions->add($height->getNumber());
        }
        
        return $dimensions;
    }
    
    public function validate_minSize($value, $width, $height, HTML_QuickForm2_Rule_Callback $rule) : bool
    {
        $helper = $this->getImageHelperByValue($value);
        if($helper === null) {
            return true;
        }
        
        // Vector images need no even sized check.
        if($helper->isVector()) {
            return true;
        }
        
        $size = $this->resolveImageSize($helper, $rule);
        if(!$size) {
            return false;
        }
        
        if($width > 0 && $size[0] < $width) {
            $rule->setMessage(t('The image\'s width is smaller than the minimum %1$s pixels.', $width));
            return false;
        }

        if($height > 0 && $size[1] < $height) {
            $rule->setMessage(t('The image\'s height is smaller than the minimum %1$s pixels.', $height));
            return false;
        }
        
        return true;
    }
}