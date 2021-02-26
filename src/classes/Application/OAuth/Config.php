<?php

declare(strict_types=1);

use function AppUtils\parseURL;

class Application_OAuth_Config
{
    /**
     * @var Application_OAuth
     */
    private $oauth;

    public function __construct(Application_OAuth $oauth)
    {
        $this->oauth = $oauth;
    }

    public function toArray(?Application_OAuth_Strategy $strategy=null) : array
    {
        $dispatcher = APP_URL.'/login.php';

        if($strategy)
        {
            $dispatcher .= '?strategy='.$strategy->getName();
        }

        $config = array(
            'callback' => $dispatcher,
            'providers' => array()
        );
        
        $strategies = $this->oauth->getStrategies();

        foreach($strategies as $strategy)
        {
            $config['providers'][$strategy->getName()] = array(
                'enabled' => true,
                'keys' => $strategy->getConfig()
            );
        }

        return $config;
    }
}
