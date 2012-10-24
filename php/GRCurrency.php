<?php
/**
 * Created by IntelliJ IDEA.
 * User: jbretting
 * Date: 10/6/12
 * Time: 8:39 AM
 * To change this template use File | Settings | File Templates.
 */



class GRCurrency {
    // from paypal's list of supported currencies
    public static $currencies = array(
        array( 'code' => 'AUD', 'symbol' => '$', 'name' => 'Australian Dollar' ),
        array( 'code' => 'BRL', 'symbol' => 'R$', 'name' => 'Brazilian Real' ),
        array( 'code' => 'CAD', 'symbol' => '$', 'name' => 'Canadian Dollar' ),
        array( 'code' => 'CZK', 'symbol' => 'Kč', 'name' => 'Czech Koruna' ),
        array( 'code' => 'DKK', 'symbol' => 'kr', 'name' => 'Danish Krone' ),
        array( 'code' => 'EUR', 'symbol' => '€', 'name' => 'Euro' ),
        array( 'code' => 'HKD', 'symbol' => '$', 'name' => 'Hong Kong Dollar' ),
        array( 'code' => 'HUF', 'symbol' => 'Ft', 'name' => 'Hungarian Forint' ),
        array( 'code' => 'ILS', 'symbol' => '₪', 'name' => 'Israeli New Sheqel' ),
        array( 'code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen' ),
        array( 'code' => 'MYR', 'symbol' => 'RM', 'name' => 'Malaysian Ringgit' ),
        array( 'code' => 'MXN', 'symbol' => '$', 'name' => 'Mexican Peso' ),
        array( 'code' => 'NOK', 'symbol' => 'kr', 'name' => 'Norweigian Krone' ),
        array( 'code' => 'NZD', 'symbol' => '$', 'name' => 'New Zealand Dollar' ),
        array( 'code' => 'PHP', 'symbol' => '₱', 'name' => 'Philippine Peso' ),
        array( 'code' => 'PLN', 'symbol' => 'zł', 'name' => 'Polish Zloty' ),
        array( 'code' => 'GBP', 'symbol' => '£', 'name' => 'Pound Sterling' ),
        array( 'code' => 'SGD', 'symbol' => '$', 'name' => 'Singapore Dollar' ),
        array( 'code' => 'SEK', 'symbol' => 'kr', 'name' => 'Swedish Krona' ),
        array( 'code' => 'CHF', 'symbol' => 'CHF', 'name' => 'Swiss Franc' ),
        array( 'code' => 'TWD', 'symbol' => 'NT$', 'name' => 'Taiwan New Dollar' ),
        array( 'code' => 'THB', 'symbol' => '฿', 'name' => 'Thai Baht' ),
        array( 'code' => 'TRY', 'symbol' => '₤', 'name' => 'Turkish Lira' ),
        array( 'code' => 'USD', 'symbol' => '$', 'name' => 'U.S. Dollar' )
    );

    public static function symbol_for_code($code) {
        foreach ( self::$currencies as $c ) {
            if ( $c['code'] == $code ) {
                return $c['symbol'];
            }
        }
        return '';
    }

    public static function name_for_code($code) {
        foreach ( self::$currencies as $c ) {
            if ( $c['code'] == $code ) {
                return $c['name'];
            }
        }
        return '';
    }

    public static function symbol() {
        return self::symbol_for_code(get_option('gr_currency_code'));
    }

    public static function name() {
        return self::name_for_code(get_option('gr_currency_code'));
    }

    public static function select_input_html($selected) {
        $html = "<select id='currency_code' name='currency_code'>";

        foreach ( self::$currencies as $c ) {
            $html .= "<option value='" . $c['code'] ."'" . ( $c['code'] == $selected ? " selected" : "" ). ">";
            $html .= "(" . $c['symbol'] . ") " . $c['code'] . " - " . $c['name'];
            $html .= "</option>";
        }

        $html .= "</select>";
        return $html;
    }
}
