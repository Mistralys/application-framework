<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

abstract class AjaxTestCase extends ApplicationTestCase
{
    private static ?string $ajaxURL = null;

    public function getAjaxURL() : string
    {
        if(isset(self::$ajaxURL)) {
            return self::$ajaxURL;
        }

        $config = __DIR__ . '/../application/config/test-ui.php';

        if(!file_exists($config)) {
            $this->fail('Test application configuration missing.');
        }

        require_once $config;

        self::$ajaxURL = TESTS_BASE_URL.'/tests/application/ajax';

        return self::$ajaxURL;
    }

    public function sendRequest(string $method, array $params=array()) : array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$this->getAjaxURL().'/?method='.$method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        if(!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $server_output = curl_exec($ch);

        $error = curl_error($ch);
        if(!empty($error)) {
            $this->fail('AJAX request failed: '.$error);
        }

        curl_close($ch);

        echo $this->getAjaxURL().'/?method='.$method.'&'.http_build_query($params).PHP_EOL;
        var_dump($server_output);
        print_r(curl_getinfo($ch));

        return json_decode($server_output, true, 512, JSON_THROW_ON_ERROR);
    }
}
