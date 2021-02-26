<?php
/**
 * File containing the {@link Application_API_Method_DescribeAdminAreas} class.
 *
 * @package Application
 * @subpackage API
 * @see Application_API_Method_DescribeAdminAreas
 */

/**
 * API method that compiles information about all administration areas
 * available in the application.
 *
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_API_Method
 */
class Application_API_Method_DescribeAdminAreas extends Application_API_Method
{
    public function getDefaultInputFormat()
    {
        return 'json';
    }
    
    public function getDefaultOutputFormat()
    {
        return 'json';
    }
    
    public function getVersions()
    {
        return array(
            '1.0.0'
        );
    }
    
    public function getCurrentVersion()
    {
        return '1.0.0';
    }
    
    protected function configure()
    {
    }
    
    public function input_json()
    {
        
    }
    
    public function output_json()
    {
        $checkSyntax = $this->request->getBool('check-syntax');
        
        $info = $this->driver->describeAdminAreas();
        $info->enableSyntaxCheck($checkSyntax);
        $info->analyzeFiles();
        
        $this->sendJSONResponse($info->toArray());
    }
}