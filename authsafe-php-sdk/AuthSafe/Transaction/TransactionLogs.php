<?php
/**
 * AuthSafe Main Class of App
 *
 */
namespace AuthSafe\Transaction;

use AuthSafe\Common\ApiCalls;
/**
 * Class Transaction
 *
 * @package Transaction
 */
class TransactionLogs
{

    private $objAPI;
    
    function __construct($objAPI)
    {
        $this->objAPI = $objAPI;
    }

    public function transactionLog($transactionEvent,$deviceId = NULL,$transactionFields = array())
    {
        $method = "POST";
        $url = '/transaction';
        $data = array(
            'ev'  => $transactionEvent,
            'dID' => $deviceId,
            'tfs' => $transactionFields
        );

            // echo '<pre>';
            // print_r($data);
            // die();

        return $this->objAPI->callAPI($method, $url, $data);
    }

}