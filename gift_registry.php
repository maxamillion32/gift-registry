<?php
/**

Plugin Name: Gift Registry
Plugin URI: http://sliverwareapps.com/registry/
Description: Adds a gift registry to WordPress. Enables you to request and track gifts, plus receive payment immediately via PayPal.
Version: v1.3
Author: sliverwareapps
Author URI: http://sliverwareapps.com
License: GPL




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

global $gr_db_version;
$gr_db_version = "1.0";

require_once dirname(__FILE__) . '/php/admin.php';
require_once dirname(__FILE__) . '/php/utils.php';

define('GR_DEFAULT_LIST_PAGE_TITLE', 'Gift Registry - Wish List');
define('GR_DEFAULT_CART_PAGE_TITLE', 'Gift Registry - Cart');


require_once('php/gr_functions.php');



class GiftRegistry {
    public static function init() {
        global $vconfig;

        $data = array(
            'Data' => array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'listUrl' => get_option( 'gr_list_url' ),
                'cartUrl' => get_option( 'gr_cart_url' ),
                'listLinkText' => get_option( 'gr_list_link_text', 'View Gift Registry Wish List' ),
                'cartLinkText' => get_option( 'gr_cart_link_text', 'View My Gift Registry Cart' )
            ),
            'Messages' => array(
                'error' => 'Sorry, an error occurred. Please go to http://sliverwareapps.com/contact for support.',
                'auth_para' => $vconfig['auth_para'],
                'auth_status' => $vconfig['auth_status']
            )
        );

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery.cookie.js', plugins_url('gift_registry/js/jquery.cookie.js'));
        wp_enqueue_script('registry.js', plugins_url('gift_registry/js/registry.js'), array( 'jquery' ));
        wp_enqueue_script('list.js', plugins_url('gift_registry/js/list.js'), array( 'jquery' ));
        wp_enqueue_script('cart.js', plugins_url('gift_registry/js/cart.js'), array( 'jquery' ));
        wp_localize_script('list.js', 'GR', $data);
        wp_enqueue_style('gr-style', plugins_url('gift_registry/css/registry.css'));
        wp_enqueue_script('mycart.js', plugins_url('gift_registry/js/mycart.js'), array( 'jquery' ));

        register_post_type( 'gr_internal',
            array(
                'labels' => array(
                    'name' => __( 'Gift Registry' ),
                    'singular_name' => __( 'Gift Registry' )
                ),
                'public' => false,
                'publicly_queryable' => true
            )
        );

    }

    public static function install() {
        global $wpdb;
        global $gr_db_version;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $installed_ver = get_option('gr_db_version');

        add_option("gr_paypal_email", "");
        add_option("gr_cart_url", "");
        add_option("gr_list_url", "");
        add_option("gr_cart_page_id", "");
        add_option("gr_list_page_id", "");
        add_option('gr_list_link_text', 'View Gift Registry Wish List');
        add_option('gr_cart_link_text', 'View My Gift Registry Cart');
        add_option('gr_gift_button_text', 'Gift It!');
        add_option('gr_custom_amount_enabled', 'n');
        add_option('gr_auth_key', '');
        add_option('gr_auth_key_valid', false);

        if( empty($installed_ver) || $installed_ver != $gr_db_version ) {
            if ( empty($installed_ver) ) {
                add_option("gr_db_version", $gr_db_version);
            } else {
                update_option("gr_db_version", $gr_db_version);
            }

            $q = "CREATE TABLE {$wpdb->prefix}registry_item (
              id int(11) NOT NULL AUTO_INCREMENT,
              title varchar(64) NOT NULL,
              descr text,
              qty_requested int(11) NOT NULL,
              price decimal(9,2) DEFAULT NULL,
              info_url varchar(256) DEFAULT NULL,
              img_url varchar(256) DEFAULT NULL,
              fulfill_dt datetime DEFAULT NULL,
              PRIMARY KEY  (id)
            )";
            dbDelta($q);

            $q = "CREATE TABLE {$wpdb->prefix}registry_order (
              id int(11) NOT NULL AUTO_INCREMENT,
              status varchar(45) NOT NULL,
              date_time datetime NOT NULL,
              total_amt decimal(9,2) NOT NULL,
              fees decimal(9,2) DEFAULT NULL,
              buyer_name varchar(64) DEFAULT NULL,
              buyer_email varchar(128) DEFAULT NULL,
              paypal_id varchar(128) DEFAULT NULL,
              comments text,
              receipt_id varchar(45) DEFAULT NULL,
              txn_id varchar(45) DEFAULT NULL,
              pay_key varchar(45) DEFAULT NULL,
              PRIMARY KEY (id)
            )";
            dbDelta($q);

            $q = "CREATE TABLE {$wpdb->prefix}registry_order_item (
              id int(11) NOT NULL AUTO_INCREMENT,
              order_id int(11) NOT NULL,
              reg_item_id int(11) NOT NULL,
              title varchar(64) NOT NULL,
              qty int NOT NULL,
              purchase_price decimal(9,2) NOT NULL,
              PRIMARY KEY  (id)
            )";
            dbDelta($q);

            $q = "CREATE TABLE `{$wpdb->prefix}registry_paypal_cart_info` (
              `txnid` varchar(30) NOT NULL default '',
              `itemname` varchar(255) NOT NULL default '',
              `itemnumber` varchar(50) default NULL,
              `os0` varchar(20) default NULL,
              `on0` varchar(50) default NULL,
              `os1` varchar(20) default NULL,
              `on1` varchar(50) default NULL,
              `quantity` char(3) NOT NULL default '',
              `invoice` varchar(255) NOT NULL default '',
              `custom` varchar(255) NOT NULL default ''
            )";
            dbDelta($q);

            $q = "CREATE TABLE `{$wpdb->prefix}registry_paypal_payment_info` (
              `firstname` varchar(100) NOT NULL default '',
              `lastname` varchar(100) NOT NULL default '',
              `buyer_email` varchar(100) NOT NULL default '',
              `street` varchar(100) NOT NULL default '',
              `city` varchar(50) NOT NULL default '',
              `state` char(3) NOT NULL default '',
              `zipcode` varchar(11) NOT NULL default '',
              `memo` varchar(255) default NULL,
              `itemname` varchar(255) default NULL,
              `itemnumber` varchar(50) default NULL,
              `os0` varchar(20) default NULL,
              `on0` varchar(50) default NULL,
              `os1` varchar(20) default NULL,
              `on1` varchar(50) default NULL,
              `quantity` char(3) default NULL,
              `paymentdate` varchar(50) NOT NULL default '',
              `paymenttype` varchar(10) NOT NULL default '',
              `txnid` varchar(30) NOT NULL default '',
              `mc_gross` varchar(6) NOT NULL default '',
              `mc_fee` varchar(5) NOT NULL default '',
              `paymentstatus` varchar(15) NOT NULL default '',
              `pendingreason` varchar(10) default NULL,
              `txntype` varchar(10) NOT NULL default '',
              `tax` varchar(10) default NULL,
              `mc_currency` varchar(5) NOT NULL default '',
              `reasoncode` varchar(20) NOT NULL default '',
              `custom` varchar(255) NOT NULL default '',
              `country` varchar(20) NOT NULL default '',
              `datecreation` date NOT NULL default '0000-00-00'
            )";
            dbDelta($q);
        }

        $current_user = wp_get_current_user();

        // check to see if our registry handling page exists
        if(!get_page_by_title('Gift Registry Transaction Complete')) {
            // create a page to handle responses from paypal
            $post = array(
                'comment_status' => 'closed', // 'closed' means no comments.
                'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
                'post_author' => $current_user->ID, //The user ID number of the author.
                'post_content' => '<!-- Automatically created by the WordPress Gift Registry Plugin; DO NOT TOUCH -->[GiftRegistry:paypalResponse]', //The full text of the post.
                'post_name' => 'Gift Registry - Transaction Complete', // The name (slug) for your post
                'post_status' => 'publish', //Set the status of the new post.
                'post_title' => 'Gift Registry Transaction Complete', //The title of your post.
                'post_type' => 'gr_internal' //You may want to insert a regular post, page, link, a menu item or some custom post type
            );

            $post_id = wp_insert_post( $post );
        }

        if(!get_page_by_title('Gift Registry Transaction Cancelled')) {
            // create a page to handle responses from paypal
            $post = array(
                'comment_status' => 'closed', // 'closed' means no comments.
                'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
                'post_author' => $current_user->ID, //The user ID number of the author.
                'post_content' => '<!-- Automatically created by the WordPress Gift Registry Plugin; DO NOT TOUCH -->[GiftRegistry:paypalCancelled]', //The full text of the post.
                'post_name' => 'Gift Registry - Transaction Cancelled', // The name (slug) for your post
                'post_status' => 'publish', //Set the status of the new post.
                'post_title' => 'Gift Registry Transaction Cancelled', //The title of your post.
                'post_type' => 'gr_internal' //You may want to insert a regular post, page, link, a menu item or some custom post type
            );

            $post_id = wp_insert_post( $post );
        }

        $list_page_id = get_option('gr_list_page_id');
        if ( !$list_page_id && !get_page_by_title(GR_DEFAULT_LIST_PAGE_TITLE) ) {
            // create a page that includes default wish list page
            $post = array(
                'comment_status' => 'closed', // 'closed' means no comments.
                'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
                'post_author' => $current_user->ID, //The user ID number of the author.
                'post_content' => "<!-- Automatically created by the WordPress Gift Registry Plugin -->\r\n<!-- Add any custom content you'd like to this page, but make sure to leave the GiftRegistry:list short code -->\r\n\r\n[GiftRegistry:list]", //The full text of the post.
                'post_name' => GR_DEFAULT_LIST_PAGE_TITLE, // The name (slug) for your post
                'post_status' => 'publish', //Set the status of the new post.
                'post_title' => GR_DEFAULT_LIST_PAGE_TITLE, //The title of your post.
                'post_type' => 'page' //You may want to insert a regular post, page, link, a menu item or some custom post type
            );

            $post_id = wp_insert_post( $post );
            $page = get_page_by_title(GR_DEFAULT_LIST_PAGE_TITLE);
            update_option('gr_list_url', get_permalink( $page->ID ));
            update_option('gr_list_page_id', $page->ID );
        }

        $cart_page_id = get_option('gr_cart_page_id');
        if ( !$cart_page_id && !get_page_by_title(GR_DEFAULT_CART_PAGE_TITLE) ) {
            // create a page that includes the default cart
            $post = array(
                'comment_status' => 'closed', // 'closed' means no comments.
                'ping_status' => 'closed', // 'closed' means pingbacks or trackbacks turned off
                'post_author' => $current_user->ID, //The user ID number of the author.
                'post_content' => "<!-- Automatically created by the WordPress Gift Registry Plugin -->\r\n<!-- Add any custom content you'd like to this page, but make sure to leave the GiftRegistry:cart short code -->\r\n\r\n[GiftRegistry:cart]", //The full text of the post.
                'post_name' => GR_DEFAULT_CART_PAGE_TITLE, // The name (slug) for your post
                'post_status' => 'publish', //Set the status of the new post.
                'post_title' => GR_DEFAULT_CART_PAGE_TITLE, //The title of your post.
                'post_type' => 'page' //You may want to insert a regular post, page, link, a menu item or some custom post type
            );

            $post_id = wp_insert_post( $post );
            $page = get_page_by_title(GR_DEFAULT_CART_PAGE_TITLE);
            update_option('gr_cart_url', get_permalink( $page->ID ));
            update_option('gr_cart_page_id', $page->ID);
        }
    }

    /************************/
    /***** AJAX METHODS *****/
    /************************/

    public static function save_auth_options() {
        $auth_key = $_POST['gr_auth_key'];
        $action = 'authentications/test/' . urlencode($auth_key) . '.json';
        $query = '?site_url=' . urlencode( site_url() ); 

        $response = gr_api_request($action, $query);
        $response = json_decode($response);

        if ( !empty($response->authentication) ) {
            echo json_encode( array(
                'message' => 'Thank you, your authentication key was verified! You may now receive gifts using this plugin'
            ));
            update_option('gr_auth_key_valid', true);
        } else {
            update_option('gr_auth_key_valid', false);
            echo json_encode( $response );
        }

        update_option( 'gr_auth_key', $auth_key );
        die();
    }

    public static function save_registry_options() {
        $cart_page = get_page( $_POST['cart_page_id'] );
        $list_page = get_page( $_POST['list_page_id'] );

        // verify methods will die and echo json if there's an error
        GiftRegistry::verify_page_selection($cart_page, 'cart');
        GiftRegistry::verify_page_selection($list_page, 'list');

        update_option( 'gr_list_page_id', $_POST['list_page_id']);
        update_option( 'gr_list_url', get_permalink($_POST['list_page_id']) );

        update_option( 'gr_cart_page_id', $_POST['cart_page_id'] );
        update_option( 'gr_cart_url', get_permalink($_POST['cart_page_id']) );

        if ( !empty($_POST['paypal_email']) ) {
            update_option('gr_paypal_email', $_POST['paypal_email']);
        }

        update_option('gr_custom_amount_enabled', $_POST['gr_custom_amount_enabled']);

        echo json_encode( array('statusCode' => 0) );
        die();
    }

    public static function save_gr_message_options() {
        if ( !empty($_POST['list_link_text']) ) {
            update_option('gr_list_link_text', $_POST['list_link_text']);
        }

        if ( !empty($_POST['cart_link_text']) ) {
            update_option('gr_cart_link_text', $_POST['cart_link_text']);
        }

        if ( !empty($_POST['gift_button_text']) ) {
            update_option('gr_gift_button_text', $_POST['gift_button_text']);
        }

        echo json_encode( array('statusCode' => 0) );
        die();
    }

    public static function add_registry_item() {
        global $wpdb; 

        unset($_POST['action']);
        unset($_POST['current_id']);

        $wpdb->insert( $wpdb->prefix . 'registry_item', $_POST );
        $registry_item = $_POST;
        $registry_item['id'] = $wpdb->insert_id;

        echo gr_item_admin_html($registry_item);
        die(); // required to return a proper ajax result
    }

    public static function delete_registry_item() {
        global $wpdb;

        $q = "delete from {$wpdb->prefix}registry_item where id = {$_POST['item_id']}";
        $wpdb->query($q);

        echo "operation successful";
        die();
    }

    public static function get_registry_item() {
        global $wpdb;

        $q = "select * from {$wpdb->prefix}registry_item where id = {$_GET['item_id']}";
        $item = $wpdb->get_row($q);

        echo json_encode($item);
        die();
    }

    public static function get_order_items() {
        global $wpdb;

        $q = "select * from {$wpdb->prefix}registry_order_item where order_id = {$_GET['order_id']}";
        $r = $wpdb->get_results($q, ARRAY_A);

        $html = "<h2>Order Items</h2>";
        $html .= "<span>Order ID:&nbsp;{$_GET['order_id']}</span>";
        $html .= "<table class='widefat'><tr><th>Title</th><th>Qty</th><th>Purchase Price</th></tr>";

        if ( count($r) ) {
            foreach ( $r as $item ) {
                $html .= "<tr>
                    <td>{$item['title']}</td>
                    <td>{$item['qty']}</td>
                    <td>{$item['purchase_price']}</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan=3>There are no items for this order</td></tr>";
        }

        $html .= "</table>";

        echo $html;
        die();
    }

    public static function update_registry_item() {
        global $wpdb; // this is how you get access to the database

        $where = array( 'id' => $_POST['current_id'] );

        unset($_POST['action']);
        unset($_POST['current_id']);

        $wpdb->update($wpdb->prefix . 'registry_item', $_POST, $where);

        echo "success!";
        die();
    }

    public static function prepare_cart() {
        global $wpdb;

        $cart = json_decode(stripslashes($_COOKIE['GR_MyCart']));
        $total = 0;
        if ( !empty($cart->items) ) {
            foreach ($cart->items as $item) {
                $total += intval($item->qty) * floatval($item->price);
            }
        }

        $order = array(
            'date_time' => date('Y-m-d h:i:s'),
            'total_amt' => $total,
            'status' => 'CREATED'
        );

        $wpdb->insert( $wpdb->prefix . 'registry_order', $order );
        $order_id = $wpdb->insert_id;

        if ( !empty($cart->items) ) {
            foreach ($cart->items as $item) {
                // item id will be empty for custom gifts
                $item_id = empty( $item->id ) ? null : $item->id;

                $order_item = array(
                    'order_id' => $order_id,
                    'reg_item_id' => $item_id,
                    'title' => $item->title,
                    'qty' => $item->qty,
                    'purchase_price' => $item->price
                );

                $wpdb->insert( $wpdb->prefix . 'registry_order_item', $order_item );
            }
        }

        $order['customId'] = $order_id;
        $order['returnUrl'] = site_url() . '?gr_internal=gift-registry-transaction-complete&customId=' . $order_id;
        echo json_encode($order);
        die();
    }

    public static function filterContent($content) {
        if (preg_match('/\[GiftRegistry:(list|cart|paypalResponse|paypalCancelled)\]/', $content, $matches)) {

            switch ($matches[1]) {
                case 'list':
                    require_once(dirname(__FILE__) . "/php/list.php");

                    $html = "<p>" . GiftRegistry::cart_link_html() . "</p>";
                    $html .= gr_list_html();

                    break;
                case 'cart':
                    require_once(dirname(__FILE__) . '/php/cart.php');

                    $cart = (!empty($_COOKIE['GR_MyCart']) ? json_decode(stripslashes($_COOKIE['GR_MyCart'])) : '');
                    $html = "<p>" . GiftRegistry::list_link_html() . "</p>";
                    $html .= gr_cart_html($cart);

                    break;
                case 'paypalResponse':
                    require_once(dirname(__FILE__) . '/php/paypal_response.php');

                    $html = gr_handle_paypal_response();

                    break;
                case 'paypalCancelled':
                    require_once(dirname(__FILE__) . '/php/paypal_cancelled.php');

                    $html = gr_handle_paypal_cancelled();

                    break;
                default:
                    $html = "";
                    break;
            }

            $content = preg_replace('/\[GiftRegistry:'.$matches['1'].'\]/', $html, $content);
        }

        return $content;
    }

    public static function gr_page_options( $selected_page_id ) {
        $options_html = '<option value="-1">(no page selected)</option>';
        $all_pages = get_pages();

        foreach ( $all_pages as $page ) {
            $sel = $page->ID == $selected_page_id ? 'selected="true"' : '';
            $options_html .= "<option $sel value='" . $page->ID . "'>" . $page->post_title . "</option>";
        }

        return $options_html;
    }

    public static function list_link_html($linkText=null) {
        $text = $linkText ? $linkText : get_option( 'gr_list_link_text' );
        $url = get_option( 'gr_list_url' );
        return !empty($url) ? "<a class='gr_list' href='$url'>$text</a>" : '';
    }

    public static function cart_link_html($linkText=null) {
        $text = $linkText ? $linkText : get_option( 'gr_cart_link_text' );
        $url = get_option( 'gr_cart_url' );
        return !empty($url) ? "<a class='gr_cart' href='$url'>$text</a>" : '';
    }

    public static function item_list() {
        global $wpdb;

        $q = "select ri.*, sum(a.qty) qty_received
            from {$wpdb->prefix}registry_item ri
            left join (
              select oi.reg_item_id, sum(oi.qty) qty
              from {$wpdb->prefix}registry_order_item oi join {$wpdb->prefix}registry_order o on oi.order_id = o.id
              where o.status = 'COMPLETED'
              group by oi.reg_item_id
            ) a on ri.id = a.reg_item_id
            group by ri.id";
        $r = $wpdb->get_results($q, ARRAY_A);

        return $r;
    }

    public static function verify_page_selection($page, $type) {
        if ( !$page || !preg_match("/\[GiftRegistry:$type\]/", $page->post_content) ) {
            echo json_encode(
                array(
                    'err' => 1,
                    'msg' => "Either the page no longer exists or it does not contain the [GiftRegistry:$type] short code. Changes were not saved.",
                    'field_name' => $type . "_page_id"
                )
            );
            die();
        }
    }
}




register_activation_hook(__FILE__, array('GiftRegistry', 'install'));

add_action('init', array('GiftRegistry', 'init'));

add_action('admin_menu', 'gr_plugin_menu');
add_action('admin_init', 'gr_admin_js');

add_action('wp_ajax_add_registry_item', array('GiftRegistry', 'add_registry_item'));
add_action('wp_ajax_save_registry_options', array('GiftRegistry', 'save_registry_options'));
add_action('wp_ajax_save_auth_options', array('GiftRegistry', 'save_auth_options'));
add_action('wp_ajax_save_gr_message_options', array('GiftRegistry', 'save_gr_message_options'));
add_action('wp_ajax_delete_registry_item', array('GiftRegistry', 'delete_registry_item'));
add_action('wp_ajax_get_registry_item', array('GiftRegistry', 'get_registry_item'));
add_action('wp_ajax_update_registry_item', array('GiftRegistry', 'update_registry_item'));
add_action('wp_ajax_prepare_cart', array('GiftRegistry', 'prepare_cart'));
add_action('wp_ajax_get_order_items', array('GiftRegistry', 'get_order_items'));

add_filter('the_content', array('GiftRegistry', 'filterContent'));


function tl_save_error() {
    file_put_contents( dirname(__FILE__) . '/install-log.html' , ob_get_contents() );
}
add_action( 'activated_plugin', 'tl_save_error' );

?>
