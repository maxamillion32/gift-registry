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

function gr_list_html() {
    $list = GiftRegistry::item_list();
    $custom_amount_enabled = get_option('gr_custom_amount_enabled');
    $custom_item_position = get_option('gr_custom_item_position');
    $list_layout = get_option('gr_list_layout');

    $html = "<div id='gr_warn_settings'><noscript>" . GR_NO_SCRIPT . "</noscript></div>";
    if (count($list) == 0 && $custom_amount_enabled == 'n') {
        $html = "There are not yet any wish list items added to this registry";
    } else {
        $custom_amount_html = ( $custom_amount_enabled == 'y' ? gr_custom_amount_enabled_html() : '' );

        $html .= "<div id='gr_wish_list' class='gr-$list_layout'>";
        foreach ($list as $item) {
            $html .= gr_item_html($item);
        }
        $html .= "</div>";

        $html = ( $custom_item_position == 'above' ? $custom_amount_html . $html : $html . $custom_amount_html );
    }

    return $html;
}

function gr_item_html($item) {
    $gift_button_text = get_option('gr_gift_button_text');
    $list_layout = get_option('gr_list_layout');

    $fulfilled = "<span class='n'>NO</span>";
    $received = $descr_link = "";
    if ( $item['qty_received'] ) {
        if ( intval($item['qty_received']) >= intval($item['qty_requested']) ) {
            $fulfilled = "<span class='y'>YES</span>";
        } else {
            $left = intval($item['qty_requested']) - intval($item['qty_received']);
            $received = "<span class='gr_received'>{$item['qty_received']} received, only <b>$left</b> left!</span>";
        }
    } 

    foreach ($item as $key => $var) {
        $$key = str_replace('$', '&#36;', stripslashes($var)); // escape $ because wp processing treats them as variables
        $json_obj[$key] = $$key;
    }

    $item_json = str_replace("'", "&apos;", json_encode($json_obj) ); // replace apostrophes to enforce valid JSON and make sure data attr isn't ended prematurely

    if ( $list_layout == 'grid' && !empty($descr) ) {
        $descr_link = "<a href='#' class='gr-descr-hover'>Details</a>";
    }

    $img_url = empty( $img_url ) ? plugins_url('gift-registry/img/custom_gift.jpg') : $img_url;
    $symbol = str_replace('$', '&#36;', GRCurrency::symbol());
    $html = "<div class='gr_item'>
                <span class='gr_item_img_wrap'><img class='gr_item_img' src='{$img_url}' alt='' /></span>
                <div class='gr_item_details'>
                    <div class='gr_item_title'><h6>{$title}</h6></div>
                    $descr_link
                    <div class='gr_item_descr'>{$descr}</div>
                    <div class='gr_item_url'><a href='{$info_url}' target='_blank'>More Info</a></div>
                    <div class='gr_item_price'>Price: <span>{$symbol}{$item['price']}</span></div>
                    <div class='gr_item_needed'>
                        Quantity Requested: <span>{$item['qty_requested']}</span>
                        $received
                    </div>
                    <div class='gr_item_filled'>Fulfilled: $fulfilled</div>
                    <button type='button' class='gr_add_to_cart_btn' data-item='{$item_json}'>{$gift_button_text}</button>
                </div>
                <div class='clear'></div>
            </div>";

    return $html;
}

function gr_custom_amount_enabled_html() {
    $gift_button_text = get_option('gr_gift_button_text');

    $symbol = GRCurrency::symbol();

    $html = "<div class='gr_item gr_custom_item'>
                <form>
                    <span class='gr_item_img_wrap'><img class='gr_item_img' src='" . plugins_url('gift-registry/img/custom_gift.jpg') . "' alt='' /></span>
                    <div class='gr_item_details'>
                        <h6 class='gr_item_title'>Custom Gift Item and Amount</h6>
                        <p>Want to give a custom item and amount? Add your item and amount here. Please note that you will have the chance to update quantities or add additional notes and wishes later.</p>
                        <ul class='gr_custom_item_form'>
                            <li>
                                <label for='gr_custom_item_title'>Custom Item Title</label>
                                <input type='text' id='gr_custom_item_title' name='gr_custom_item_title' />
                            </li>
                            <li>
                                <label for='gr_custom_item_price'>Custom Amount&nbsp;($symbol)</label>
                                <input type='text' id='gr_custom_item_price' name='gr_custom_item_price' />
                            </li>
                            <li><button type='button' class='gr_custom_add_to_cart_btn'>{$gift_button_text}</button></li>
                        </ul>
                    </div>
                    <!-- <div class='clear'></div> -->
                </form>
            </div>";

    return $html;
}

?>