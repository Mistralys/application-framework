<?php
/**
 * File containing the {@see UI_DataGrid_RedirectMessage} class.
 * 
 * @package UI
 * @subpackage DataGrid
 * @see UI_DataGrid_RedirectMessage
 */

declare(strict_types=1);

use Application\Driver\DriverException;
use Application\Interfaces\Admin\AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * Class used to handle messages after a datagrid action:
 * Chooses the right message to display according to the
 * amount of records that were affected by the operation,
 * and redirects to the target URL, either with a success
 * styled message, or an info styled message if none were
 * affected.
 *
 * @package UI
 * @subpackage DataGrid
 */
class UI_DataGrid_RedirectMessage
{
   /**
    * @var string
    */
    protected $redirectURL = '';

   /**
    * @var array
    */
    protected $messages = array(
        'none' => '',
        'single' => '',
        'multiple' => ''
    );
    
    protected AdminScreenInterface $screen;
    
   /**
    * @var string[]
    */
    protected $processed = array();
    
   /**
    * @var UI_DataGrid_Action
    */
    protected $action;

    /**
     * @var callable|NULL
     */
    protected $deletableCallback = null;

    /**
     * @param UI_DataGrid_Action $action
     * @param string|AdminURLInterface $redirectURL
     * @throws DriverException
     */
    public function __construct(UI_DataGrid_Action $action, $redirectURL)
    {
        $this->redirectURL = (string)$redirectURL;
        $this->action = $action;
        $this->screen = Application_Driver::getInstance()->requireActiveScreen();
        
        $this
        ->none(t('No elements were affected by the operation.'))
        ->single(t('1 element was affected by the operation.'))
        ->multiple(t('$amount elements were affected by the operation.'));
    }

    /**
     * Sets a callback to use to check whether a record should be
     * included or not when using the `processDeleteDBRecords`
     * method. The method should return `true` if it can be deleted,
     * and `false` otherwise.
     *
     * Gets the record as a single parameter.
     *
     * @param callable $callback
     * @return $this
     * @throws Application_Exception
     */
    public function setDeletableCallback($callback) : UI_DataGrid_RedirectMessage
    {
        Application::requireCallableValid($callback);

        $this->deletableCallback = $callback;

        return $this;
    }
    
   /**
    * Sets the message text to use when no records 
    * were affected.
    * 
    * Possible placeholders to use in the text: 
    * - $amount 
    * - $time
    * 
    * @param string $message
    * @return UI_DataGrid_RedirectMessage
    */
    public function none(string $message) : UI_DataGrid_RedirectMessage
    {
        return $this->setMessage('none', $message);
    }

   /**
    * Sets the message text to use when a single 
    * record was affected.
    * 
    * Possible placeholders to use in the text:
    *  
    * - $amount 
    * - $time
    * - $label
    * 
    * @param string $message
    * @return UI_DataGrid_RedirectMessage
    */
    public function single(string $message) : UI_DataGrid_RedirectMessage
    {
        return $this->setMessage('single', $message);
    }
    
   /**
    * Sets the message text to use when several
    * record were affected.
    * 
    * Possible placeholders to use in the text: 
    * - $amount 
    * - $time
    *
    * @param string $message
    * @return UI_DataGrid_RedirectMessage
    */
    public function multiple(string $message) : UI_DataGrid_RedirectMessage
    {
        return $this->setMessage('multiple', $message);
    }
    
    protected function setMessage(string $type, string $message) : UI_DataGrid_RedirectMessage
    {
        $this->messages[$type] = $message;
        
        return $this;
    }
    
    public function countAffected() : int
    {
        return count($this->processed);
    }
    
    public function addAffected(string $label) : UI_DataGrid_RedirectMessage
    {
        $this->processed[] = $label;
        
        return $this;
    }
    
    public function redirect()
    {
        $amount = $this->countAffected();
        
        if($amount === 0)
        {
            $this->screen->redirectWithInfoMessage(
                $this->getMessage('none'),
                $this->redirectURL
            );
        }
        
        if($amount === 1)
        {
            $this->screen->redirectWithSuccessMessage(
                $this->getMessage('single'),
                $this->redirectURL
            );
        }
        
        $this->screen->redirectWithSuccessMessage(
            $this->getMessage('multiple'),
            $this->redirectURL
        );
    }
    
    protected function getMessage(string $type) : string
    {
        $text = $this->messages[$type];
        
        $replaces = $this->getPlaceholders();
        
        return str_replace(
            array_keys($replaces), 
            array_values($replaces), 
            $text
        );
    }
    
    public function getPlaceholders() : array
    {
        $label = '';
        
        if(!empty($this->processed)) 
        {
            reset($this->processed);
            
            $label = $this->processed[key($this->processed)];
        }
        
        return array(
            '$amount' => $this->countAffected(),
            '$time' => date('H:i:s'),
            '$label' => $label
        );
    }
    
   /**
    * Automates deleting selected DBHelper records, when working with
    * the DBHelper_Collection. Goes through the selected IDs, and deletes
    * the relevant records withing a transaction.
    * 
    * @param DBHelper_BaseCollection $collection
    * @return UI_DataGrid_RedirectMessage
    */
    public function processDeleteDBRecords(DBHelper_BaseCollection $collection) : UI_DataGrid_RedirectMessage
    {
        $ids = $this->action->getSelectedValues();
        
        $this->screen->startTransaction();
        
        foreach($ids as $id)
        {
            $id = (int)$id;
            
            if(!$collection->idExists($id))
            {
                continue;
            }

            $record = $collection->getByID($id);

            if(isset($this->deletableCallback) && call_user_func($this->deletableCallback, $record) !== true)
            {
                continue;
            }
          
            $collection->deleteRecord($record);
            
            $this->addAffected($record->getLabel());
        }

        $this->screen->endTransaction();
        
        return $this;
    }
}
