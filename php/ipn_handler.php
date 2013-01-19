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
require_once '../settings.php';
require_once '../../../../wp-blog-header.php'; // to get wpdb


// Revision Notes
// 11/04/11 - changed post back url from https://www.paypal.com/cgi-bin/webscr to https://ipnpb.paypal.com/cgi-bin/webscr
// For more info see below:
// https://www.x.com/content/bulletin-ip-address-expansion-paypal-services
// "ACTION REQUIRED: if you are using IPN (Instant Payment Notification) for Order Management and your IPN listener script is behind a firewall that uses ACL (Access Control List) rules which restrict outbound traffic to a limited number of IP addresses, then you may need to do one of the following:
// To continue posting back to https://www.paypal.com  to perform IPN validation you will need to update your firewall ACL to allow outbound access to *any* IP address for the servers that host your IPN script
// OR Alternatively, you will need to modify  your IPN script to post back IPNs to the newly created URL https://ipnpb.paypal.com using HTTPS (port 443) and update firewall ACL rules to allow outbound access to the ipnpb.paypal.com IP ranges (see end of message)."


/////////////////////////////////////////////////
/////////////Begin Script below./////////////////
/////////////////////////////////////////////////

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
    $$key = $value;
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen (GR_IPN_URL, 443, $errno, $errstr, 30);

/**
 * assign posted variables to local variables
$item_name = $_POST['item_name'];
$business = $_POST['business'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$mc_gross = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$receiver_id = $_POST['receiver_id'];
$quantity = $_POST['quantity'];
$num_cart_items = $_POST['num_cart_items'];
$payment_date = $_POST['payment_date'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$payment_type = $_POST['payment_type'];
$payment_status = $_POST['payment_status'];
$payment_gross = $_POST['payment_gross'];
$payment_fee = $_POST['payment_fee'];
$settle_amount = $_POST['settle_amount'];
$memo = $_POST['memo'];
$payer_email = $_POST['payer_email'];
$txn_type = $_POST['txn_type'];
$payer_status = $_POST['payer_status'];
$address_street = $_POST['address_street'];
$address_city = $_POST['address_city'];
$address_state = $_POST['address_state'];
$address_zip = $_POST['address_zip'];
$address_country = $_POST['address_country'];
$address_status = $_POST['address_status'];
$item_number = $_POST['item_number'];
$tax = $_POST['tax'];
$option_name1 = $_POST['option_name1'];
$option_selection1 = $_POST['option_selection1'];
$option_name2 = $_POST['option_name2'];
$option_selection2 = $_POST['option_selection2'];
$for_auction = $_POST['for_auction'];
$invoice = $_POST['invoice'];
$custom = $_POST['custom'];
$notify_version = $_POST['notify_version'];
$verify_sign = $_POST['verify_sign'];
$payer_business_name = $_POST['payer_business_name'];
$payer_id = $_POST['payer_id'];
$mc_currency = $_POST['mc_currency'];
$mc_fee = $_POST['mc_fee'];
$exchange_rate = $_POST['exchange_rate'];
$settle_currency = $_POST['settle_currency'];
$parent_txn_id = $_POST['parent_txn_id'];
$pending_reason = $_POST['pending_reason'];
$reason_code = $_POST['reason_code'];
*/

/**
 * subscription specific vars
$subscr_id = $_POST['subscr_id'];
$subscr_date = $_POST['subscr_date'];
$subscr_effective = $_POST['subscr_effective'];
$period1 = $_POST['period1'];
$period2 = $_POST['period2'];
$period3 = $_POST['period3'];
$amount1 = $_POST['amount1'];
$amount2 = $_POST['amount2'];
$amount3 = $_POST['amount3'];
$mc_amount1 = $_POST['mc_amount1'];
$mc_amount2 = $_POST['mc_amount2'];
$mc_amount3 = $_POST['mcamount3'];
$recurring = $_POST['recurring'];
$reattempt = $_POST['reattempt'];
$retry_at = $_POST['retry_at'];
$recur_times = $_POST['recur_times'];
$username = $_POST['username'];
$password = $_POST['password'];
*/

/**
 * auction specific vars

$for_auction = $_POST['for_auction'];
$auction_closing_date = $_POST['auction_closing_date'];
$auction_multi_item = $_POST['auction_multi_item'];
$auction_buyer_id = $_POST['auction_buyer_id'];
*/


if (!$fp) {
    // HTTP ERROR
    logToFile("HTTP ERROR DURING IPN\r\n$req");
} else {
    fputs($fp, $header . $req);

    while (!feof($fp)) {
        $res = fgets($fp, 1024);
        if (strcmp($res, "VERIFIED") == 0 || !empty($force_bypass)) {
           if (!$wpdb) {
                logToFile( "Couldn't connect to MySQL:\r\nERROR:" . mysql_error() . " - " . mysql_errno() );
                die();
            }

            $fecha = date("m") . "/" . date("d") . "/" . date("Y");
            $fecha = date("Y") . date("m") . date("d");


            // Gift Registry code
	        // update transaction record even if it's a duplicate
            $q = "update {$wpdb->prefix}registry_order set
                status = 'COMPLETED',
                buyer_email = '$payer_email',
                fees = '$mc_fee'
                where id = " . mysql_real_escape_string($custom);
            logToFile("IPN Received; SQL Query to mark transaction as COMPLETED: \r\n\t$q");
            $result = $wpdb->query($q);

            if (!$result) {
                logToFile('WPDB ERROR: ' . $wpdb->last_error);
            }

            logToFile("VERIFIED IPN:\n $req");

	    //check if transaction ID has been processed before
            $checkquery = "select txnid from {$wpdb->prefix}registry_paypal_payment_info where txnid='" . $txn_id . "'";
            $sihay = $wpdb->get_var($checkquery);
            if ($sihay) {
                logToFile("Duplicate txn id check query failed for txnid $txn_id:\r\nMySQL Error:" . mysql_error() . " - " . mysql_errno());
                die();
            }

            if ( $wpdb->num_rows == 0 ) {
                if ($txn_type == "cart") {
                    $strQuery = "insert into {$wpdb->prefix}registry_paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('" . $payment_status . "','" . $payer_email . "','" . $first_name . "','" . $last_name . "','" . $address_street . "','" . $address_city . "','" . $address_state . "','" . $address_zip . "','" . $address_country . "','" . $mc_gross . "','" . $mc_fee . "','" . $memo . "','" . $payment_type . "','" . $payment_date . "','" . $txn_id . "','" . $pending_reason . "','" . $reason_code . "','" . $tax . "','" . $fecha . "')";

                    $result = $wpdb->query($strQuery);
                    if (!$result) {
                        logToFile("Cart - {$wpdb->prefix}registry_paypal_payment_info, Query failed:\r\n" . mysql_error() . "\r\n" . mysql_errno());
                        die();
                    }
                    for ($i = 1; $i <= $num_cart_items; $i++) {
                        $itemname = "item_name" . $i;
                        $itemnumber = "item_number" . $i;
                        $on0 = "option_name1_" . $i;
                        $os0 = "option_selection1_" . $i;
                        $on1 = "option_name2_" . $i;
                        $os1 = "option_selection2_" . $i;
                        $quantity = "quantity" . $i;

                        $struery = "insert into {$wpdb->prefix}registry_paypal_cart_info(txnid,itemnumber,itemname,os0,on0,os1,on1,quantity,invoice,custom) values ('" . $txn_id . "','" . $_POST[$itemnumber] . "','" . $_POST[$itemname] . "','" . $_POST[$on0] . "','" . $_POST[$os0] . "','" . $_POST[$on1] . "','" . $_POST[$os1] . "','" . $_POST[$quantity] . "','" . $invoice . "','" . $custom . "')";
                        $result = $wpdb->query($struery);
                        if (!$result) {
                            logToFile("Cart - {$wpdb->prefix}registry_paypal_cart_info, Query failed with error:" . mysql_error() . " - " . mysql_errno());
                            die();
                        }
                    }
                } else {
                    $strQuery = "insert into {$wpdb->prefix}registry_paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('" . $payment_status . "','" . $payer_email . "','" . $first_name . "','" . $last_name . "','" . $address_street . "','" . $address_city . "','" . $address_state . "','" . $address_zip . "','" . $address_country . "','" . $mc_gross . "','" . $mc_fee . "','" . $item_number . "','" . $item_name . "','" . $option_name1 . "','" . $option_selection1 . "','" . $option_name2 . "','" . $option_selection2 . "','" . $quantity . "','" . $memo . "','" . $payment_type . "','" . $payment_date . "','" . $txn_id . "','" . $pending_reason . "','" . $reason_code . "','" . $tax . "','" . $fecha . "')";
                    $result = $wpdb->query("insert into wp_registry_paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('" . $payment_status . "','" . $payer_email . "','" . $first_name . "','" . $last_name . "','" . $address_street . "','" . $address_city . "','" . $address_state . "','" . $address_zip . "','" . $address_country . "','" . $mc_gross . "','" . $mc_fee . "','" . $item_number . "','" . $item_name . "','" . $option_name1 . "','" . $option_selection1 . "','" . $option_name2 . "','" . $option_selection2 . "','" . $quantity . "','" . $memo . "','" . $payment_type . "','" . $payment_date . "','" . $txn_id . "','" . $pending_reason . "','" . $reason_code . "','" . $tax . "','" . $fecha . "')") or die("Default - wp_registry_paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
                }
            } else {
                logToFile("VERIFIED DUPLICATED TRANSACTION:\r\n$res\n $req \n $strQuery\n $struery\n  $strQuery2");
            }

            //subscription handling branch
            if ($txn_type == "subscr_signup" || $txn_type == "subscr_payment") {

                // insert subscriber payment info into wp_registry_paypal_payment_info table
                $strQuery = "insert into {$wpdb->prefix}registry_paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('" . $payment_status . "','" . $payer_email . "','" . $first_name . "','" . $last_name . "','" . $address_street . "','" . $address_city . "','" . $address_state . "','" . $address_zip . "','" . $address_country . "','" . $mc_gross . "','" . $mc_fee . "','" . $memo . "','" . $payment_type . "','" . $payment_date . "','" . $txn_id . "','" . $pending_reason . "','" . $reason_code . "','" . $tax . "','" . $fecha . "')";
                $result = $wpdb->query($strQuery) or die("Subscription - {$wpdb->prefix}registry_paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());

                // insert subscriber info into wp_registry_paypal_subscription_info table
                $strQuery2 = "insert into {$wpdb->prefix}registry_paypal_subscription_info(subscr_id , sub_event, subscr_date ,subscr_effective,period1,period2, period3, amount1 ,amount2 ,amount3,  mc_amount1,  mc_amount2,  mc_amount3, recurring, reattempt,retry_at, recur_times, username ,password, payment_txn_id, subscriber_emailaddress, datecreation) values ('" . $subscr_id . "', '" . $txn_type . "','" . $subscr_date . "','" . $subscr_effective . "','" . $period1 . "','" . $period2 . "','" . $period3 . "','" . $amount1 . "','" . $amount2 . "','" . $amount3 . "','" . $mc_amount1 . "','" . $mc_amount2 . "','" . $mc_amount3 . "','" . $recurring . "','" . $reattempt . "','" . $retry_at . "','" . $recur_times . "','" . $username . "','" . $password . "', '" . $txn_id . "','" . $payer_email . "','" . $fecha . "')";
                $result = $wpdb->query($strQuery2) or die("Subscription - {$wpdb->prefix}registry_paypal_subscription_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());

                logToFile("VERIFIED IPN\r\n$res\n $req\n $strQuery\n $struery\n  $strQuery2");
            }
        } else if (strcmp($res, "INVALID") == 0) {
            // log for manual investigation
            logToFile("INVALID IPN: \r\n$res\r\n $req");
        }
    }

    fclose($fp);
}
?>

