<?php
/**
 * AuthSafe Main Class of App
 *
 */
namespace AuthSafe\Login;

use AuthSafe\Common\ApiCalls;
/**
 * Class Login
 *
 * @package Login
 */
class LoginLogs
{

    private $objAPI;
    
    function __construct($objAPI)
    {
        $this->objAPI = $objAPI;
    }

    public function loginLog($loginEvent,$userId = NULL,$deviceId = NULL,$userExtras = array())
    {
        $method = "POST";
        $url = '/login';
        $data = array(
            'ev'  => $loginEvent,
            'uID' => $userId,
            'dID' => $deviceId,
            'uex' => $userExtras
        );

        // echo '<pre>';
        // print_r($data);
        // die();

        return $this->objAPI->callAPI($method, $url, $data);
    }

}