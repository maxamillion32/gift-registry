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

function gr_admin_js() {
    wp_enqueue_script('jquery.lightbox_me.js', plugins_url('gift_registry/js/jquery.lightbox_me.js'), array( 'jquery' ));
    wp_enqueue_script('jquery.validate.min.js', plugins_url('gift_registry/js/jquery.validate.min.js'), array( 'jquery' ));
    wp_enqueue_script('admin.js', plugins_url('gift_registry/js/admin.js'), array( 'jquery' ));
}

function gr_plugin_menu() {
    add_options_page('Gift Registry', 'Gift Registry', 'manage_options', 'gift-registry-menu', 'gr_options' );
}

function gr_options() {
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    echo '<div class="wrap gr-admin-wrap">';
    echo '<div class="widget-liquid-left">';

    echo "<div class='widget-liquid-right'>";
    gr_admin_quick_start(); 
    gr_admin_version_widgets();
    gr_links();
    echo "</div>";

    gr_admin_registry_options();
    gr_admin_messages_form();
    gr_admin_registry_item_form();
    gr_admin_registry_item_list();
    gr_admin_order_list();

    echo "<div id='gr_lightbox'></div>";
    echo '</div>';
    echo '</div>';
}

function gr_admin_registry_instructions() {
    $hide = empty($_COOKIE['GR_instruction_state']) ? false : true;
    ?>
    <div class='gr-instructions'>
        <h2>Instructions</h2>
        <div class='gr-info <?php echo $hide ? 'gr_hidden' : ''; ?>'>
            <p>Hello and thanks for installing the WordPress Gift Registry Plugin! This plugin is designed to be extremely intuitive to use.</p>
            <p><b>However, there are some critical items that must be configured in advance or the plugin <span style='color: red;'>JUST WON'T WORK</span>.</b></p>
            <p>Relax, it's easy. Cruise on over to the Gift Registry Plugin <a href='http://sliverwareapps.com/site/registry' target=_blank>instructions</a> before diving in.</p>
        </div>
        <a id='gr-instructions-toggle' href='#'><?php echo $hide ? 'Expand' : 'Close'; ?></a>
    </div>
    <?php
}

function gr_links() {
    $hide = empty($_COOKIE['GR_quick_start_state']) || $_COOKIE['GR_quick_start_state'] != 'hidden' ? 'Hide' : 'Show';
    ?>
    <div class='gr-instructions'>
        <h2>Links</h2>
        <ul>
            <li><a id='gr_show_quick_start' class='gr_quick_start_toggle' href='#'><?php echo $hide; ?>&nbsp;Quick Start</a></li>
            <li><a href='http://sliverwareapps.com/registry/' target=_blank>Documentation</a></li>
            <li><a href='http://sliverwareapps.com/registry/#faq'>FAQ</a></li>
        </ul>

    </div>
    <?php
}

function gr_admin_registry_options() {
    $list_page_id = get_option('gr_list_page_id');
    $cart_page_id = get_option('gr_cart_page_id');
    $paypal_email = get_option('gr_paypal_email');
    $custom_amount_enabled = get_option('gr_custom_amount_enabled');

    $cart_options = GiftRegistry::gr_page_options( $cart_page_id );
    $list_options = GiftRegistry::gr_page_options( $list_page_id );

    $cart_page = get_page( $cart_page_id );
    $list_page = get_page( $list_page_id );

    $cart_error = $list_error = 0;

    if ( !$cart_page || !preg_match("/\[GiftRegistry:cart\]/", $cart_page->post_content) ) {
        $cart_error = 1;
    }

    if ( !$list_page || !preg_match("/\[GiftRegistry:list\]/", $list_page->post_content) ) {
        $list_error = 1;
    } 

    ?>
    <div class='gr-options-form gr-admin-form'>
        <h2>Gift Registry Options</h2>
        <form id='gr_options_form'>
            <input type='hidden' name='action' value='save_registry_options' />
            <ul>
                <li>
                    <label for='paypal_email'>PayPal Email Address</label>
                    <input type='text' id='paypal_email' name='paypal_email' value='<?php echo $paypal_email; ?>' />
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'><p>To enable people to send you payments, enter the email address associated with your PayPal account.</p><p>Please note that this plugin will not distribute this information in any way.</p></div>
                    </div>
                </li>
                <li <?php echo $list_error ? "class='gr_page_err'" : ''; ?>'>
                    <label for='list_page_id_select'>Gift Registry Wish List Page</label>
                    <select name='list_page_id' id='list_page_id_select'>
                        <?php echo $list_options; ?>
                    </select>
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'>
                            <p>Select the page that contains the [GiftRegistry:list] short code.</p>
                            <p>You will need to link to this page so guests can access your Gift Registry.</p>
                        </div>
                        <div class='gr_help gr_err'>
                            <p>An error has been detected with your configuration</p>
                            <p>Make sure that you have selected a page the contains the [GiftRegistry:list] short code.</p>
                            <p>This page should have been created for you when the plugin was installed, but it's possible it has been inadvertently modified.</p>
                        </div>
                    </div>
                </li>
                <li <?php echo $cart_error ? "class='gr_page_err'" : ''; ?>'>
                    <label for='cart_page_id_select'>Gift Registry Cart Page</label>
                    <select name='cart_page_id' id='cart_page_id_select'>
                        <?php echo $cart_options; ?>
                    </select>
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'>
                            <p>Select the page that contains the [GiftRegistry:cart] short code.</p>
                            <p>The Wish List page will contain a link to this page, but you may also link to it elsewhere if you like.</p>
                        </div>
                        <div class='gr_help gr_err'>
                            <p>An error has been detected with your configuration</p>
                            <p>Make sure that you have selected a page the contains the [GiftRegistry:cart] short code.</p>
                            <p>Note that this page should have been created for you when the plugin was installed, but it's possible it has been inadvertently modified.</p>
                        </div>
                    </div>
                </li>
                <li>
                    <label for='gr_custom_amount_enabled'>Allow Guests to Enter Custom Gift Amount</label>
                    <select id='gr_custom_amount_enabled' name='gr_custom_amount_enabled'>
                        <option value='n' <?php echo $custom_amount_enabled == 'n' ? "selected" : '' ?>>No</option>
                        <option value='y' <?php echo $custom_amount_enabled == 'y' ? "selected" : '' ?>>Yes</option>
                    </select>

                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'>
                            <p>Enabling this option allows guests to specify a custom gift amount via an item that appears on the registry wish list.<p>
                            <p>They will be able to name the item and add additonal notes during their checkout with PayPal.</p>
                        </div>
                    </div>
                </li>
                <li class='buttons'>
                    <input type='button' class='button-primary' id='save_options_btn' value='Save' />
                </li>
            </ul>
        </form>
    </div>

<?php
}

function gr_admin_messages_form() {
    // TODO: add option to reset messages links
    $list_link_text = get_option('gr_list_link_text');
    $cart_link_text = get_option('gr_cart_link_text');
    $gift_button_text = get_option('gr_gift_button_text');

    ?>
    <div class='gr-options-form gr-admin-form'>
        <h2>Message Options</h2>
        <form id='gr_messages_form'>
            <input type='hidden' name='action' value='save_gr_message_options' />
            <ul>
                <li>
                    <label for='list_link_text'>Wish List Link Text</label>
                    <input type='text' id='list_link_text' name='list_link_text' value='<?php echo $list_link_text; ?>' />
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'><p>This text will appear on links to your wish list.</p></div>
                    </div>
                </li>
                <li>
                    <label for='cart_link_text'>Cart Link Text</label>
                    <input type='text' id='cart_link_text' name='cart_link_text' value='<?php echo $cart_link_text; ?>' />
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'><p>This text will appear on links to the shopping cart.</p></div>
                    </div>
                </li>
                <li>
                    <label for='gift_button_text'>Gift Button Text</label>
                    <input type='text' id='gift_button_text' name='gift_button_text' value='<?php echo $gift_button_text; ?>' />
                    <div class='gr_field_info'>
                        <div class='gr_help gr_info'><p>This text will appear on the button that prompts users to add a registry item to their cart.</p></div>
                    </div>
                </li>
                <li class='buttons'>
                    <input type='button' class='button-primary' id='save_messages_btn' value='Save' />
                </li>
            </ul>
        </form>
    </div>
<?php
}

function gr_admin_registry_item_form() { ?>
<img id='gr-img-preload' />
<div class='gr-add-item-form gr-admin-form'>
    <h2 id='gr_item_form_title'>Add a Registry Item</h2>
    <form id='registry_item_form'>
        <input type='hidden' name='action' value='add_registry_item' />
        <input type='hidden' name='current_id' value='' />
        <ul>
            <li>
                <label for='title'>Title</label>
                <input type='text' id='title' name='title' />
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>Add a title to use for your item.</div>
                </div>
            </li>
            <li>
                <label for='descr'>Description</label>
                <textarea id='descr' name='descr' cols=39 rows=1></textarea>
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>Add a description to your item to give your guests some additional information.</div>
                </div>
            </li>
            <li>
                <label for='info_url'>Info Url</label>
                <input type='text' id='info_url' name='info_url'/>
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>Enter a link that your guests can click to get more information about this item.</div>
                </div>
            </li>
            <li>
                <label for='img_url'>Image Url</label>
                <div class='gr-img-preview'>
                    <input type='text' id='img_url' name='img_url' />
                    <div id='img-preview-wrap'>
                        <img id='img-preview' height='75px' width='115px' src='<?php echo plugins_url('gift_registry/img/placeholder.gif'); ?>' />
                    </div>
                    <span>Provide a url to an image file. Once provided, a preview will display in a placeholder to the right.</span>
                    <a class='clear_img' href='#'>Clear Image</a>
                </div>
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>You can enter a url to any image on the web. If you wish to upload your own images, make sure this is an absolute url to the image hosted on your site.</div>
                </div>
            </li>
            <li>
                <label for='qty_requested'>Quantity Requested</label>
                <input type='text' id='qty_requested' name='qty_requested' />
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>You may update the quantity requested for a particular item at any time. However, you will not be able to adjust the number of items you have received without manually updating the database.</div>
                </div>
            </li>
            <li>
                <label for='price'>Price Each</label>
                <input type='text' id='price' name='price' />
                <div class='gr_field_info'>
                    <div class='gr_help gr_info'>Enter the price you are requesting for each of these items. You may change the price of the item at any time.</div>
                </div>
            </li>
            <li class='buttons'>
                <input type='submit' class='button-primary' id='save_item_btn' value='Add Item' />
                <input type='button' class='button-primary' id='clear_item_btn' value='Clear Form' />
            </li>
        </ul>
    </form>
</div>
<?php
    }

function gr_admin_registry_item_list() {
    $itemList = GiftRegistry::item_list();

?>
<h2>Your Registry Items</h2>
<table id='registry_items' class='widefat'>
    <tr><th>Title</th><th>Qty Requested</th><th>Qty Received</th><th>Each ($)</th></tr>
    <?php
        if ( count($itemList) > 0 ) {
            foreach ($itemList as $registry_item) {
                echo gr_item_admin_html($registry_item);
            }
        } else {
            $html = "<tr class='gr_info'><td colspan=4>You have not added any items to your registry list. Get started by using the Add Registry Item form above!</td></tr>";
            echo $html;
        }
    ?>
</table>
<?php
}

function gr_item_admin_html($item) {
    $price = number_format($item['price'], 2);
    $received = !empty($item['qty_received']) ? $item['qty_received'] : 0;

    return <<<HTML
<tr data-registry_item_id='{$item['id']}' id='item_row_{$item['id']}'>
    <td>
        <span class='gr_item_title'>{$item['title']}</span>
        <div class='row-actions'>
            <span class='edit'><a href='#'>Edit</a></span>&nbsp;|
            <span class='delete'><a href='#'>Delete</a></span>
        </div>
    </td>
    <td class='gr_item_qty_req'>{$item['qty_requested']}</td>
    <td>{$received}</td>
    <td class='gr_item_price'>{$price}</td>
</tr>
HTML;
}

function gr_admin_order_list() {
    global $wpdb;

    $q = "select * from {$wpdb->prefix}registry_order where status in ('COMPLETED', 'RECEIVED', 'IPN ERROR')";
    $r = $wpdb->get_results($q, ARRAY_A);

    ?>
<h2>Gifts Received</h2>
<table id='registry_orders' class='widefat'>
    <tr><th>ID</th><th>Status</th><th>Date</th><th>From</th><th>Total</th><th>Fees</th><th></th></tr>
    <?php
        foreach ($r as $order) {
            echo gr_order_html($order);
        }
    ?>
<?php
}

function gr_order_html($order) {
    return <<<HTML
<tr data-order_id='{$order['id']}'>
    <td>{$order['id']}</td>
    <td>{$order['status']}</td>
    <td>{$order['date_time']}</td>
    <td>{$order['buyer_email']}</td>
    <td>{$order['total_amt']}</td>
    <td>{$order['fees']}</td>
    <td><a class='order_items' href='#'>Items</td></td>
</tr>
HTML;

}
?>