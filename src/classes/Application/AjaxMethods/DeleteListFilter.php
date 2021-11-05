<?php

class Application_AjaxMethods_DeleteListFilter extends Application_AjaxMethod
{
    public const ERROR_INVALID_FILTER_ID = 470001;
    public const ERROR_INVALID_SETTINGS_ID = 470002;
    
    public function processJSON()
    {
        $this->request->registerParam('settings_id')->setRegex('/([0-9a-zA-Z_-]+)/');
        $this->request->registerParam('filter_id')->setInteger();
        
        $filter_id = $this->request->getParam('filter_id');
        if(empty($filter_id)) {
            $this->sendErrorUnknownElement(t('Filter ID'), null, self::ERROR_INVALID_FILTER_ID);
        }
        
        $settings_id = $this->request->getParam('settings_id');
        if(empty($settings_id)) {
            $this->sendErrorUnknownElement(t('Filter settings identifier'), null, self::ERROR_INVALID_SETTINGS_ID);
        }
        
        $settingName = $settings_id.'_presets';
        $response = array(
            'filter_id' => $filter_id,
            'settings_id' => $settings_id,
            'existed' => false
        );
        
        $filters = $this->user->getArraySetting($settingName);
        if(empty($filters)) {
            $this->sendResponse($response);
        }
        
        if(!isset($filters[$filter_id])) {
            $this->sendResponse($response);
        }
        
        unset($filters[$filter_id]);        
        
        $this->user->setArraySetting($settingName, $filters);
        $this->user->saveSettings();
        
        $response['existed'] = true;
        
        $this->sendResponse($response);
    }
}