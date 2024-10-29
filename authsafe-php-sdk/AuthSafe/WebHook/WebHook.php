<?php
/**
 * AuthSafe Main Class of App
 *
 */
namespace AuthSafe\WebHook;

use AuthSafe\Common\ApiCalls;
/**
 * Class WebHook
 *
 * @package WebHook
 */
class WebHook
{

    private $objAPI;
    
    function __construct($objAPI)
    {
        $this->objAPI = $objAPI;
    }

    public function addWebHook($webhookURL)
    {
        $method = "POST";
        $url = '/webhooks';
        $data = array(
            'url'  => $webhookURL
        );

        return $this->objAPI->callAPI($method, $url, $data);
    }

}