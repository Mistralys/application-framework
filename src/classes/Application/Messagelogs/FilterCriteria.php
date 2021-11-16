<?php

/**
 * @package Application
 * @subpackage Core 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @method Application_Messagelogs_Log[] getItemsObjects()
 */
class Application_Messagelogs_FilterCriteria extends DBHelper_BaseFilterCriteria
{
    protected function prepareQuery() : void
    {
        $this->addDateSearch('date', '`date`');
        
        $this->addWhereColumnIN('`category`', $this->getCriteriaValues('categories'));
        
        $this->addWhereColumnIN('`type`', $this->getCriteriaValues('types'));
        
        $this->addWhereColumnIN('`user_id`', $this->getCriteriaValues('user_ids'));
    }
    
   /**
    * @param string $category
    * @return Application_Messagelogs_FilterCriteria
    */
    public function selectCategory(string $category)
    {
        return $this->selectCriteriaValue('categories', $category);
    }
    
   /**
    * @param string $type
    * @return Application_Messagelogs_FilterCriteria
    */
    public function selectType(string $type)
    {
        return $this->selectCriteriaValue('types', $type);
    }
    
   /**
    * @param integer $user_id
    * @return Application_Messagelogs_FilterCriteria
    */
    public function selectUser($user_id)
    {
        return $this->selectCriteriaValue('user_ids', $user_id);
    }
    
   /**
    * @param string $dateSearchString
    * @return Application_Messagelogs_FilterCriteria
    */
    public function selectDate(string $dateSearchString)
    {
        return $this->selectCriteriaDate('date', $dateSearchString);
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void
    {
        // TODO: Implement _registerStatementValues() method.
    }

    protected function _registerJoins() : void
    {
        // TODO: Implement _registerJoins() method.
    }
}