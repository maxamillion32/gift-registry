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

define('GR_LOG_FILE', dirname(__FILE__) . '/log');

function logToFile($msg, $echo=false, $altLogFile=null, $error=false) {
	$logFile = ($altLogFile ? $altLogFile : GR_LOG_FILE);

	$loc = getCodeLocation(1);

	if ($echo) echo "<br>$msg\r\n";
	$fd = fopen($logFile, "a");
	$str = "[" . date("Y/m/d h:i:s") . "] $loc > $msg";
    $str .= ($error ? "\r\nERROR >\r\n".serverInfo() : "");
	fwrite($fd, $str . "\r\n");
	fclose($fd);
	flush();
	if (ob_get_level() > 0) { ob_flush(); }
}

function serverInfo() {
    $out = "";
    foreach (array('HTTP_USER_AGENT', 'HTTP_REFERER', 'REMOTE_ADDR', 'REQUEST_METHOD', 'REQUEST_URI') as $key) {
        $out .= "[$key] => ".$_SERVER[$key]."\r\n";
    }
    return $out;
}

// lvl indicates whether you want to display the calling function or not.
// ex: if this function is called from logToFile and you do not want to show logToFile in the log, set lvl to 1, otherwise set to 0
function getCodeLocation($lvl) {
	$dbbt = debug_backtrace();
	$loc = "";
	for ($i = 0; $i < count($dbbt); $i++) {
		if (($i + 1) == count($dbbt)) {
			$loc = $dbbt[$i]['file'] . '('.$dbbt[$i]['line'].')' . " > ".$dbbt[$i]['function'] . $loc;
		} else if ($i > $lvl) {
			$loc = '('.chk_empty($dbbt[$i], 'line').') > '.$dbbt[$i]['function'] . $loc;
		} else if ($i == $lvl) {
			$loc = '('.chk_empty($dbbt[$i], 'line').')';
		}
	}

	return $loc;
}

function chk_empty($arr, $key) {
    if (empty($arr[$key])) {
        return "";
    } else {
        return $arr[$key];
    }
}


function gr_api_request($action, $query, $method = 'GET') {
    $url = GR_AUTH_SERVER_URL . '/' . $action;
    $query .= '&method=' . $method;

    try {
        if ( strtoupper($method) != 'GET' ) {

            if ( function_exists('curl_init') ) {
                $httpResponse = curl_post_contents($url, $query);
            } else if ( ini_get('allow_url_fopen') ) {
                $httpResponse = file_post_contents($url . $query);
            } else { // error
                throw new Exception('Neither curl or fopen wrappers are enabled, no way to establish authentication server. Please contact your hosting provider.');
            }

        } else {

            if ( function_exists('curl_init') ) {
                $httpResponse = curl_get_contents($url, $query);
            } else if ( ini_get('allow_url_fopen') ) {
                $httpResponse = file_get_contents($url . $query);
            } else { // error
                throw new Exception('Neither curl or fopen wrappers are enabled, no way to establish authentication server. Please contact your hosting provider.');
            }

        }

        if(!$httpResponse) {
            throw new Exception("Unexpected empty response from auth server after successful connection. Check server logs for warnings.");
        }
    } catch(Exception $ex) {
        logToFile("ERROR during request:\r\n\turl: $url\r\n\tquery: $query");
        logToFile($ex->getMessage());

        echo json_encode(
            array(
                'error' => 1,
                'message' => $ex->getMessage()
            )
        );
        die();
    }

    return $httpResponse;
}

function curl_post_contents($url, $query) {
    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // Turn off the server and peer verification (TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    // Set the request as a POST FIELD for curl.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

    // Get response from the server.
    $httpResponse = curl_exec($ch);

    if (!$httpResponse) {
        throw new Exception("authorization api request failed: ".curl_error($ch).'('.curl_errno($ch).')');
    }

    return $httpResponse;
}

function curl_get_contents($url, $query) {
    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $query);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    // Turn off the server and peer verification (TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Get response from the server.
    $httpResponse = curl_exec($ch);

    if (!$httpResponse) {
        throw new Exception("authorization api request failed: ".curl_error($ch).'('.curl_errno($ch).')');
    }

    return $httpResponse;
}


function file_post_contents($url,$headers=false) {
    $url = parse_url($url);

    if (!isset($url['port'])) {
      if ($url['scheme'] == 'http') { $url['port']=80; }
      elseif ($url['scheme'] == 'https') { $url['port']=443; }
    }
    $url['query'] = isset($url['query']) ? $url['query'] : '';

    $url['protocol']=$url['scheme'].'://';
    $eol="\r\n";

    $headers =  "POST ".$url['protocol'].$url['host'].$url['path']." HTTP/1.0".$eol.
                "Host: ".$url['host'].$eol.
                "Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
                "Content-Type: application/x-www-form-urlencoded".$eol.
                "Content-Length: ".strlen($url['query']).$eol.
                $eol.$url['query'];
    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

    if($fp) {
        fputs($fp, $headers);
        $result = '';
        while(!feof($fp)) { $result .= fgets($fp, 128); }
        fclose($fp);
        if (!$headers) {
            //removes headers
            $pattern="/^.*\r\n\r\n/s";
            $result=preg_replace($pattern,'',$result);
        }
        return $result;
    }
}