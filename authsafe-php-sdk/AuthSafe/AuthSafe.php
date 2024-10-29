<?php
/**
 *  AuthSafe Main Class of App
 *
 */

namespace AuthSafe;

use AuthSafe\Login\LoginLogs;
use AuthSafe\Transaction\TransactionLogs;
use AuthSafe\Login\ResetPasswordLogs;
use AuthSafe\Register\RegisterLogs;
use AuthSafe\Common\ApiCalls;
use AuthSafe\Common\ErrorMessage;
use AuthSafe\Device\DeviceManage;
use AuthSafe\WebHook\WebHook;


/**
 * Class AuthSafe
 *
 * @package AuthSafe
 */
class AuthSafe
{
    /**
     * @const string Version number of the AuthSafe PHP SDK.
     */
    const VERSION = '1.2.0';

    /**
     * @const string The name of the environment variable that contains the app ID.
     */
    const APP_ID_ENV_NAME = 'AUTHSAFE_APP_ID';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    const APP_SECRET_ENV_NAME = 'AUTHSAFE_APP_SECRET';

    /**
     * @const string The name of the environment variable that contains the app secret.
     */
    //const APP_API_URL = 'https://pixel.authsafe.ai';

    /**
     * @var AuthSafeApp The AuthSafeApp entity.
     */
    protected $app;

    /**
     * Instantiates a new AuthSafe super-class object.
     *
     * @param array $config
     *
     * @throws AuthSafeSDKException
     */
    public $loginLog;
    public $transactionLog;
    public $resetPasswordLog;
    public $registerLog;
    public $objAPI;
    public $apiUrl;

    public function __construct(array $config = [])
    { 
        $msg = array();
        $a = new ErrorMessage();
        $this->apiUrl = 'https://a.authsafe.ai/v1';
        //$this->apiUrl = 'http://127.0.0.1:5000';
        $config = array_merge([
            'property_id' => getenv(static::APP_ID_ENV_NAME),
            'property_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'api_url' => $this->apiUrl
        ], $config);
        
        $this->objAPI = new ApiCalls($config['property_id'],$config['property_secret'],$config['api_url']);
        $this->loginLog = new LoginLogs($this->objAPI);
        $this->transactionLog = new TransactionLogs($this->objAPI);
        $this->resetPasswordLog = new ResetPasswordLogs($this->objAPI);
        $this->registerLog = new RegisterLogs($this->objAPI);
        $this->deviceManage = new DeviceManage($this->objAPI);
        $this->webHook = new WebHook($this->objAPI);

        if(empty($config['property_id']) || !isset($config['property_id']))
        {
            $msg['status'] = "error";
            $erno = 0;
            $msg['message'] = $a->errormsg($erno);
            return $msg;
        }

        if(empty($config['property_secret']) || !isset($config['property_secret']))
        {
            $msg['status'] = "error";
            $erno = 0;
            $msg['message'] = $a->errormsg($erno);
            return $msg;
        }

    }

    public function loginAttempt($loginEvent,$userId = NULL,$deviceId = NULL,$userExtras = array())
    {
       
        $b = new ErrorMessage();

        if(empty($loginEvent) || !isset($loginEvent))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }
        if(($loginEvent == 'login_success') && (empty($userId) || !isset($userId)))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->loginLog->loginLog($loginEvent,$userId,$deviceId,$userExtras);
    }

    public function transactionAttempt($transactionEvent,$deviceId = NULL,$transactionExtras = array())
    {

        $b = new ErrorMessage();

        if(empty($transactionEvent) || !isset($transactionEvent))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }
        return $this->transactionLog->transactionLog($transactionEvent,$deviceId,$transactionExtras);
    }

    public function registerAttempt($registerEvent,$deviceId = NULL,$registerFields = array())
    {
        $b = new ErrorMessage();

        if(empty($registerEvent) || !isset($registerEvent))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }
        if(count($registerFields) == 0)
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->registerLog->registerLog($registerEvent,$deviceId,$registerFields);
    }

    public function passwordResetAttempt($resetPasswordEvent,$userId = NULL,$deviceId = NULL,$userExtras = array())
    {
        $b = new ErrorMessage();

        if(empty($resetPasswordEvent) || !isset($resetPasswordEvent))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }
        if(($resetPasswordEvent == 'reset_password_success') && (empty($userId) || !isset($userId)))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->resetPasswordLog->resetPasswordLog($resetPasswordEvent,$userId,$deviceId,$userExtras);

    }

    public function approveDevice($deviceId)
    {
        $b = new ErrorMessage();

        if(empty($deviceId) || !isset($deviceId))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->deviceManage->deviceManage($deviceId, "approve");
    }

    public function denyDevice($deviceId)
    {
        $b = new ErrorMessage();

        if(empty($deviceId) || !isset($deviceId))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->deviceManage->deviceManage($deviceId, "deny");
    }

    public function getUserDevices($userId)
    {
        $b = new ErrorMessage();

        if(empty($userId) || !isset($userId))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->deviceManage->getUserDevices($userId);
    }

    public function addWebHook($url)
    {
        $b = new ErrorMessage();

        if(empty($url) || !isset($url))
        {
            $msg['status'] = "error";
            $erno = 1;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            $msg['status'] = "error";
            $erno = 3;
            $msg['message'] = $b->errormsg($erno);
            return $msg;
        }

        return $this->webHook->addWebHook($url);
    }
}