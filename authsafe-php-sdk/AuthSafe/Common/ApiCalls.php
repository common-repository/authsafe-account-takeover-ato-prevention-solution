<?php
/**
 * AuthSafe API Calls Class of App
 *
 */
namespace AuthSafe\Common;

/**
 * Class ApiCalls
 *
 * @package ApiCalls
 */
class ApiCalls
{

    private $property_id;
    private $property_secret;
    private $apiURL;
    
    function __construct($property_id,$property_secret,$app_url)
    {
        // echo $app_url;
        $this->property_id = $property_id;
        $this->property_secret = $property_secret;
        $this->apiURL = $app_url;
    }

    private function getAPIUrl($path)
    {
        return $this->apiURL.$path;
    }

    public function callAPI($method, $url, $data){

        try {
            $headers = array();
            $headers['ip'] = $this->get_client_ip();
            $headers['ua'] = $_SERVER['HTTP_USER_AGENT'];
            $headers['ho'] = $_SERVER['HTTP_HOST'];
            if (isset($_SERVER['HTTP_REFERER'])) {
                $headers['rf'] = $_SERVER['HTTP_REFERER'];
            }
            $headers['ac'] = $this->getAcceptString($_SERVER);
            $headers['url'] = $this->getPageUrl($_SERVER);
            //$headers['head'] = getallheaders();

            // echo '<pre>';
            // print_r($headers);
            // die();

            $data['h'] = $headers;
            
            $url = $this->getAPIUrl($url);
            // echo '<pre>';
            // print_r($data);
            // echo '<br>';
            // echo $url;
            // die();
            $curl = curl_init();

            if(!$curl) {
                $response["status"] = "error";
                $response["message"] = "Couldn't initialize a cURL handle";
            } else {

                switch ($method){
                    case "POST":
                        curl_setopt($curl, CURLOPT_POST, true);
                        if ($data)
                            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                        break;
                    case "PUT":
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                        if ($data)
                            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                        break;
                    default:
                        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
                        if ($data) {
                            curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($data) );
                        }
                }

                $apiKey = base64_encode($this->property_id.':'.$this->property_secret);

                // echo $apiKey;
                // die();

                // OPTIONS:
                @curl_setopt($curl, CURLOPT_URL, $url);
                @curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "authorization: Basic ".$apiKey,
                    "content-type: application/json"
                ));
                @curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                @curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                //@curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                // EXECUTE:

                $result = @curl_exec($curl);
                //if(!$result){die("Connection Failure");}

                if($headers['ip'] == "45.252.74.134") {
                    // var_dump($result);
                    // echo curl_errno($curl);
                    // echo '<br />';
                    // echo curl_getinfo($curl, CURLINFO_HTTP_CODE);
                }

                $response = array();
                // Check the return value of curl_exec(), too
                if ($result === false) {
                    $response["status"] = "error";
                    $response["message"] = curl_error($curl);
                } else {
                    $response = json_decode($result, true);
                }

                curl_close($curl);
            }
            // echo '<pre>';
            // print_r($response);
            // die();
           
            return $response;
           
        } catch(Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
    }

    // Function to get the client IP address
    public function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    private function getPageOrigin( $srv, $use_forwarded_host = false )
    {
        // check if SSL
        $ssl      = (!empty($srv['HTTPS'])&&$srv['HTTPS']=='on');
        $srvp     = strtolower( $srv['SERVER_PROTOCOL'] );
        $srvpro   = substr( $srvp, 0, strpos( $srvp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
        $port     = $srv['SERVER_PORT'];
        $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
        $host     = ( $use_forwarded_host && isset( $srv['HTTP_X_FORWARDED_HOST'] ) ) ? $srv['HTTP_X_FORWARDED_HOST'] : ( isset( $srv['HTTP_HOST'] ) ? $srv['HTTP_HOST'] : null );
        $host     = isset( $host ) ? $host : $srv['SERVER_NAME'] . $port;
        return $srvpro . '://' . $host;
    }

    private function getPageUrl( $srv, $use_forwarded_host = false )
    {
        return $this->getPageOrigin( $srv, $use_forwarded_host ) . $srv['REQUEST_URI'];
    }

    public function getAcceptString($srv)
    {
        $retData = array();
        if (isset($srv['HTTP_ACCEPT'])) {
            $retData['a'] = $_SERVER['HTTP_ACCEPT'];
        }
        if (isset($srv['HTTP_ACCEPT_CHARSET'])) {
            $retData['ac'] = $_SERVER['HTTP_ACCEPT_CHARSET'];
        }
        if (isset($srv['HTTP_ACCEPT_ENCODING'])) {
            $retData['ae'] = $_SERVER['HTTP_ACCEPT_ENCODING'];
        }
        if (isset($srv['HTTP_ACCEPT_LANGUAGE'])) {
            $retData['al'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        return $retData;
    }

}