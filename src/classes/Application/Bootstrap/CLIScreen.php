<?php

use GetOpt\Command;
use GetOpt\Option;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;

abstract class Application_Bootstrap_CLIScreen extends Application_Bootstrap_Screen
{
   /**
    * @var GetOpt
    */
    protected $opt;
    
    protected function _boot()
    {
        $this->disableAuthentication();
        $this->enableScriptMode();
        $this->createEnvironment();
        
        $this->opt = new GetOpt();
        
        $this->opt->addOption(
            Option::create('?', 'help', GetOpt::NO_ARGUMENT)
            ->setDescription('Display this command help.')
        );
        
        $this->configureCommands();
        
        try
        {
            $this->opt->process();
        }
        catch (ArgumentException $exception)
        {
            echo 'Exception: '.$exception->getMessage().PHP_EOL.PHP_EOL;
            echo $this->opt->getHelpText();
            Application::exit();
        }
        
        $command = $this->resolveCommand();
        
        if (!$command || $this->opt->getOption('help'))
        {
            echo $this->opt->getHelpText();
            Application::exit();
        }
        else
        {
            try
            {
                call_user_func($command->getHandler(), $command, $this->opt);
            }
            catch(Exception $e)
            {
                displayError($e);
            }
        }
    }
    
    private function resolveCommand() : ?Command
    {
        $command = $this->opt->getCommand();

        if($command instanceof Command) {
            return $command;
        }

        return null;
    }
    
   /**
    * Used to set up all available command line switches
    */
    abstract protected function configureCommands();

   /**
    * Logs a text to the console, with a newline appended.
    * 
    * @param mixed ...$params
    */
    protected function log(...$params)
    {
        echo call_user_func_array('sprintf', $params).PHP_EOL;
    }
    
   /**
    * Logs a text to the console, without newline appended.
    * 
    * @param mixed ...$params
    */
    protected function ilog(...$params)
    {
        echo call_user_func_array('sprintf', $params);
    }
}
