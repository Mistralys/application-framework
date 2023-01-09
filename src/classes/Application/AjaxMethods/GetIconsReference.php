<?php

declare(strict_types=1);

use AppUtils\FileHelper\JSONFile;

class Application_AjaxMethods_GetIconsReference extends Application_AjaxMethod
{
    public function processJSON() : void
    {
        $files = array(
            array(
                'id' => 'default',
                'label' => t('Default icons'),
                'path' => __DIR__.'/../../../themes/default/icons.json'
            ),
            array(
                'id' => 'custom',
                'label' => t('Custom icons'),
                'path' => APP_ROOT.'/themes/custom-icons.json'
            )
        );

        $payload = array();

        foreach($files as $fileDef)
        {
            $file = JSONFile::factory($fileDef['path']);

            if(!$file->exists()) {
                continue;
            }

            $icons = $file->parse();

            foreach($icons as $id => $iconDef)
            {
                $iconDef['sourceID'] = $fileDef['id'];
                $iconDef['sourceLabel'] = $fileDef['label'];

                $payload[$id] = $iconDef;
            }
        }

        $this->sendResponse($payload);
    }
}