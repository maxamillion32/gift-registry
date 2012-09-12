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

function gr_handle_paypal_response() {
    global $wpdb;
    
    wp_enqueue_script('paypal_response.js', plugins_url('gift_registry/js/paypal_response.js'), array( 'jquery' ));
    $customId = mysql_real_escape_string($_GET['customId']);

    $q = "update {$wpdb->prefix}registry_order set
        status = 'RECEIVED'
        where id = '$customId'";
    $wpdb->query($q);

    $html = "<p>Thank you for your gift!</p>";
    $html .= "<p>Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at <a href='http://www.paypal.com' target=_blank>www.paypal.com</a> to view details of this transaction.</p>";

    return $html;
}

?>