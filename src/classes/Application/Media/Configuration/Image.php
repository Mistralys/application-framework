<?php

class Application_Media_Configuration_Image extends Application_Media_Configuration
{
    public function addPreset($label, $alias, $type, $width, $height)
    {
        $presets = $this->getData('presets', array());
        
        $presets[] = array(
            'label' => $label,
            'alias' => $alias,
            'type' => $type,
            'width' => $width,
            'height' => $height
        );
        
        $this->setData('presets', $presets);
    }
    
   /**
    * (non-PHPdoc)
    * @see Application_Media_Configuration::isProcessRequired()
    */
    public function isProcessRequired(Application_Media_Document $document)
    {
        $this->requireMatchingType($document);
        
        $presets = $this->getData('presets', array());
        
        foreach($presets as $preset) {
            if(!$document->thumbnailExists($preset['width'], $preset['height'])) {
                $this->log(sprintf('Process is required for preset [%s].', $preset['alias']));
                return true;
            }
        }
        
        return false;
    }
    
    public function process(Application_Media_Document $document)
    {
        $this->requireMatchingType($document);
        
        $presets = $this->getData('presets', array());
        
        foreach($presets as $preset) {
            $document->createThumbnail($preset['width'], $preset['height']);
        }
    }
}