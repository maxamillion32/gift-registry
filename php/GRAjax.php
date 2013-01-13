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

class GRAjax {

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
        GRAjax::verify_page_selection($cart_page, 'cart');
        GRAjax::verify_page_selection($list_page, 'list');

        update_option( 'gr_list_page_id', $_POST['list_page_id']);
        update_option( 'gr_list_url', get_permalink($_POST['list_page_id']) );

        update_option( 'gr_cart_page_id', $_POST['cart_page_id'] );
        update_option( 'gr_cart_url', get_permalink($_POST['cart_page_id']) );

        update_option( 'gr_list_layout', $_POST['gr_list_layout'] );

        if ( !empty($_POST['paypal_email']) ) {
            update_option('gr_paypal_email', $_POST['paypal_email']);
        }

        update_option('gr_currency_code', $_POST['currency_code']);
        update_option('gr_custom_amount_enabled', $_POST['gr_custom_amount_enabled']);
        update_option('gr_custom_item_position', $_POST['gr_custom_item_position']);

        $currency = array(
            'symbol' => GRCurrency::symbol(),
            'name' => GRCurrency::name(),
            'code' => $_POST['currency_code']
        );

        echo json_encode( array(
            'statusCode' => 0,
            'currency' => $currency
        ));
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

        $_POST['price'] = str_replace('$', '', $_POST['price']);

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

        $q = $wpdb->prepare("select * from {$wpdb->prefix}registry_item where id = %d", $_GET['item_id']);
        $item = $wpdb->get_row($q);

        foreach ( $item as $key => $prop ) {
            $item->$key = stripslashes( $prop );
        }

        echo json_encode($item);
        die();
    }

    public static function get_order_items() {
        global $wpdb;

        $q = $wpdb->prepare("select * from {$wpdb->prefix}registry_order_item where order_id = %d", $_GET['order_id']);
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

        $_POST['price'] = str_replace('$', '', $_POST['price']);

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
