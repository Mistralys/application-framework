<?php

use AppUtils\ConvertHelper;

class Application_AjaxMethods_GetChangelogRevisions extends Application_AjaxMethod
{
    public function processJSON()
    {
        $revisionableTypes = $this->driver->getRevisionableTypes();
        
        $this->request->registerParam('owner_primary')->setArray();
        $this->request->registerParam('type_name')->setEnum($revisionableTypes);
        
        $primary = $this->request->getParam('owner_primary');
        $type = $this->request->getParam('type_name');
        
        if(empty($primary) || empty($type)) {
            $this->sendError(t('Missing parameters.'), array('allowed_types' => $revisionableTypes));
        }
        
        $revisionable = null;
        
        try
        {
            $revisionable = $this->driver->getRevisionable($type, $primary);
        } 
        catch(Exception $e) 
        {
            $this->sendError(t('The specified parameters did not match any known revisionable.'));            
        }
        
        $payload = array(
            'owner_primary' => $primary,
            'type_name' => $type,
            'stateless' => $revisionable instanceof Application_Revisionable,
            'revisions' => array()
        );

        $revisions = $revisionable->getRevisions();
        foreach($revisions as $revision) {
            $revisionable->selectRevision($revision);
            $data = array(
                'revision' => $revision,
                'pretty_revision' => $revisionable->getPrettyRevision(),
                'date' => ConvertHelper::date2listLabel($revisionable->getRevisionDate(), true, true),
                'timestamp' => $revisionable->getRevisionTimestamp(),
                'comments' => $revisionable->getRevisionComments(),
                'owner_name' => $revisionable->getOwnerName(),
                'owner_id' => $revisionable->getOwnerID(),
                'amount_changes' => $revisionable->countChangelogEntries()                
            );
            
            if($revisionable instanceof Application_Revisionable) 
            {
                $data['state'] = $revisionable->getStateName();
                $data['state_label_pretty'] = $revisionable->getCurrentPrettyStateLabel();
                $data['state_label'] = $revisionable->getCurrentStateLabel();
            }
            
            $payload['revisions'][] = $data;
        }
        
        $this->sendResponse($payload);
    }
}