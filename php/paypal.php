<?php
/**
Copyright 2012 Sliverware Applications, Inc

This file is part of the WordPress Gift Registry Plugin.

WordPress Gift Registry Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

WordPress Gift Registry Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress Gift Registry Plugin.  If not, see <http://www.gnu.org/licenses/>.
*/


require_once 'utils.php';


class PayPal {

    //set PayPal Endpoint to sandbox
    private $API_Url = "https://svcs.paypal.com/AdaptivePayments/"; // GET RESPONSE INDICATING UNSPECIFIED METHOD
//    private $API_Url = "https://svcs.sandbox.paypal.com/AdaptivePayments/";

    //PayPal API Credentials
    private $API_UserName = ""; //TODO
    private $API_Password = ""; //TODO
    private $API_Signature = ""; //TODO

    //Default App ID for Sandbox
//    private $API_AppID = "APP-80W284485P519543T";

    private $API_RequestFormat = "NV";
    private $API_ResponseFormat = "NV";

    private $request;
    private $defaults;

    private $op;

    function __construct() {
        $returnUrl = site_url() . '?gr_internal=gift-registry-transaction-complete&payKey=${payKey}';
        $cancelUrl = site_url() . '?gr_internal=gift-registry-transaction-cancelled&payKey=${payKey}';

        $this->defaults = array(
            "currencyCode" => "USD",
            "requestEnvelope.errorLanguage" => "en_US",
            'cancelUrl' => $cancelUrl,
            'returnUrl' => $returnUrl
        );
    }

    public function setRequest($req) {
        $this->op = $req['op'];
        $this->request = array_merge( $this->defaults, $req['params'] );
    }

    public function sendRequest() {
        // convert payload array into url encoded query string
        $request_data = http_build_query($this->request, "", chr(38));

        try {
            //create request and add headers
            $params = array("http" => array(
                "method" => "POST",
                "content" => $request_data,
                "header" => "X-PAYPAL-SECURITY-USERID: " . $this->API_UserName . "\r\n" .
                            "X-PAYPAL-SECURITY-SIGNATURE: " . $this->API_Signature . "\r\n" .
                            "X-PAYPAL-SECURITY-PASSWORD: " . $this->API_Password . "\r\n" .
//                            "X-PAYPAL-APPLICATION-ID: " . $this->API_AppID . "\r\n" .
                            "X-PAYPAL-REQUEST-DATA-FORMAT: " . $this->API_RequestFormat . "\r\n" .
                            "X-PAYPAL-RESPONSE-DATA-FORMAT: " . $this->API_ResponseFormat . "\r\n"
            ));

            $ctx = stream_context_create($params); //create stream context
            $fp = @fopen($this->API_Url . $this->op, "r", false, $ctx);  //open the stream and send request
            $response = stream_get_contents($fp);  //get response

            //check to see if stream is open
            if ($response === false) {
                throw new Exception("php error message = " . "$php_errormsg");
            }

            //close the stream
            fclose($fp);

            logToFile( $response );

            //parse the ap key from the response
            $kArray = PayPal::parseNvp($response);
            logToFile( print_r($kArray, true) );

            //print the response to screen for testing purposes
            if ($kArray["responseEnvelope"]['ack'] != "Success") {
                echo 'ERROR Code: ' . $kArray["error"][0]['errorId'] . " <br/>";
                echo 'ERROR Message: ' . urldecode($kArray["error"][0]["message"]) . " <br/>";
            }

            return $kArray;
            
        } catch (Exception $e) {
            echo "Message: ||" . $e->getMessage() . "||";
        }
    }

    private static function parseNvp($response) {
        $nvpArray = explode("&", $response);

        $arr = array();
        foreach ($nvpArray as $pair) {
            list($key, $val) = explode("=", $pair);

            $arr = PayPal::parseValue($arr, $key, urldecode($val));
        }

        return $arr;
    }

    private static function parseValue($arr, $key, $val) {
        $pos = strpos($key, '.');
        if ( $pos === false ) {
            $arr[$key] = $val;
        } else if ( preg_match("/^([^\.]*)\(([^\)]*)\)\.([^\.]*)$/", $key, $matches ) ) { // key(n).key
            $arr[$matches[1]][$matches[2]][$matches[3]] = $val;
        } else if ( preg_match("/^([^\.]*)\(([^\)]*)\)\.(.*)$/", $key, $matches) ) { // key(n).key[.key]
            $arr[$matches[1]][$matches[2]] = !empty($arr[$matches[1]][$matches[2]]) ? $arr[$matches[1]][$matches[2]] : array();
            $arr[$matches[1]][$matches[2]] = PayPal::parseValue($arr[$matches[1]][$matches[2]], $matches[3], $val);
        } else if ( preg_match("/^([^\.]*)\.([^\.]*)$/", $key, $matches) ) { // key.key
            $arr[$matches[1]][$matches[2]] = $val;
        } else if ( preg_match("/^([^\.]*)\.(.*)$/", $key, $matches) ) { // key.key[.key]
            $arr[$matches[1]] = !empty($arr[$matches[1]]) ? $arr[$matches[1]] : array();
            $arr[$matches[1]] = PayPal::parseValue($arr[$matches[1]], $matches[2], $val);
        } else {
            die("Unrecognized pattern for key: $key");
        }

        return $arr;
    }
}



