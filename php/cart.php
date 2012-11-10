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

function gr_cart_html($cart) {
    $html = "<div id='gr_warn_settings'><noscript>" . GR_NO_SCRIPT . "</noscript></div>";

    $html .= "<div id='gr_cart_wrap'><h2>Your Registry Cart</h2>";
    $html .= "<div class='gr_clear_wrap'>";

    $html .= "<form id='gr_cart_form' action='" . GR_PAYPAL_URL . "/cgi-bin/webscr' method='post'>";
    $html .= "<table id='gr_cart_tbl'>";
    $html .= "<tr><th>Item Name</th><th>Qty</th><th>Each</th><th>Total</th><th></th></tr>";

    if ( !empty($cart->items) ) {
        $i = 1; // because starting at 0 paypal doesn't recognize the cart
        $cartTotal = 0;
        foreach ( $cart->items as $item ) {
            $tot = intval($item->qty) * floatval($item->price);
            $cartTotal += $tot;

            $each = str_replace('$', '\$', GRCurrency::symbol() . number_format(floatval($item->price), 2));
            $totalStr = str_replace('$', '\$', GRCurrency::symbol() . number_format($tot, 2));
            $item->title = str_replace('$', '\$', $item->title);
            $plus_minus = "<span class='gr_incr_wrap'><span class='gr_incr gr_plus'></span><span class='gr_incr gr_minus'></span></span>";

            $html .= "<tr class='gr_cart_item' data-item_id='$i'>";
            $html .= "<td>" . $item->title . "</td>";
            $html .= "<td><input type='number' class='gr_qty' name='quantity_$i' value={$item->qty} />$plus_minus</td>";
            $html .= "<td class='gr_each'>" . $each . "</td>";
            $html .= "<td class='gr_tot'>$totalStr</td>";
            $html .= "<td><a href='#' class='gr_delete'>Remove</a></td>";
            $html .= "</tr>";

            $i++;
        }

        $cartTotal = str_replace('$', '\$', GRCurrency::symbol() . number_format($cartTotal, 2));

    } else {
        $html .= "<tr class='cart_empty'><td colspan='5'>Your Cart Is Empty</td></tr>";

        $cartTotal = str_replace('$', '\$', GRCurrency::symbol() . number_format(0, 2));
    }

    $html .= "<tr class='gr_cart_summary'><td>Cart Total</td><td></td><td></td><td><span id='gr_cart_total'>" . $cartTotal . "</span></td><td></td></tr>";
    $html .= "</table>";

    $html .= gr_button_html(); // from gr_functions.php
    $html .= "<div class='loading_icon'><img src='" . plugins_url('gift-registry/img/ajax-loader-med.gif') . "' alt='loading' /></div>";

    $html .= "<input type='button' id='gr_clear_cart' value='Empty Cart' />";
    $html .= "<input type='button' id='gr_update_cart_btn' value='Update Total' />"; // doesn't have functionality, just a button to trigger change/blur events of qty input
    $html .= "</form>";

    $html .= "</div></div><!-- gr_cart_wrap -->";

    return $html;
}



?>