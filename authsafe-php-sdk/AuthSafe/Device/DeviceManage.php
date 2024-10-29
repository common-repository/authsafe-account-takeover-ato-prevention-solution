<?php
/**
 * AuthSafe Main Class of App
 *
 */
namespace AuthSafe\Device;

use AuthSafe\Common\ApiCalls;
/**
 * Class Login
 *
 * @package Login
 */
class DeviceManage
{

    private $objAPI;
    
    function __construct($objAPI)
    {
        $this->objAPI = $objAPI;
    }

    public function deviceManage($deviceId, $action)
    {
        // echo $deviceId;
        // die();
        $deviceId = urlencode($deviceId);
        $method = "POST";
        $url = "/devices/{$deviceId}/{$action}";
        $data = array();

        return $this->objAPI->callAPI($method, $url, $data);
    }

    public function getUserDevices($userId)
    {
        $method = "GET";
        $url = "/users/{$userId}/devices";
        $data = array();

        return $this->objAPI->callAPI($method, $url, $data);
    }

}