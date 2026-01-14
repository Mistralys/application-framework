<?php

class Application_AjaxMethods_SaveListFilter extends Application_AjaxMethod
{
    public const string METHOD_NAME = 'SaveListFilter';

    public const int ERROR_INVALID_LABEL = 460001;
    public const int ERROR_INVALID_SETTINGS_ID = 460002;
    public const int ERROR_NO_SETTINGS_SPECIFIED = 460003;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function processJSON()
    {
        $this->request->registerParam('label')->setRegex(AppUtils\RegexHelper::REGEX_LABEL);
        $this->request->registerParam('settings_id')->setRegex('/([0-9a-zA-Z_-]+)/');
        $this->request->registerParam('settings')->setArray();
        
        $label = $this->request->getParam('label');
        if(empty($label)) {
            $this->sendErrorUnknownElement(t('Label'), null, self::ERROR_INVALID_LABEL);
        }
        
        $settings_id = $this->request->getParam('settings_id');
        if(empty($settings_id)) {
            $this->sendErrorUnknownElement(t('Filter settings identifier'), null, self::ERROR_INVALID_SETTINGS_ID);
        }
        
        $settings = $this->request->getParam('settings');
        if(empty($settings)) {
            $this->sendError(t('No settings specified'), null, self::ERROR_NO_SETTINGS_SPECIFIED);
        }

        $settingName = $settings_id.'_presets';
        $filters = array();
        $filterID = 1;
        $new = true;
        
        $filters = $this->user->getArraySetting($settingName);

        if(!empty($filters)) {
            $filterID = max(array_keys($filters)) + 1;
        }
        
        // if the label already exists, we want to overwrite the existing entry.
        foreach($filters as $id => $def) {
            if($def['label'] == $label) {
                $filterID = $id;
                $new = false;
                break;
            }
        }
        
        $filters[$filterID] = array(
            'id' => $filterID,
            'label' => $label,
            'settings' => $settings
        );
        
        $this->user->setArraySetting($settingName, $filters);
        $this->user->saveSettings();
        
        $response = array(
            'id' => $filterID,
            'label' => $label,
            'was_new' => $new,
            'settings' => $settings
        );
        
        $this->sendResponse($response);
    }
}
