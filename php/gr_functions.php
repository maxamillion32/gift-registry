<?php

$vconfig = array(
    'auth_para' => array(
        'verified' => '<p>Thank you for purchasing the WordPress Gift Registry Plugin. Guests should now be able to purchase gifts
                 via the <a href="' . get_option( 'gr_cart_url' ) . '" target="_blank">cart page</a> on your site.</p>',
        'unverified' => '<p>In order to receive gifts using this plugin, you will need to <a href="' . GR_AUTH_SERVER_URL . '">purchase
                 an authentication key</a>. Once you have purchased your key, enter it into the field below. If the
                 authentication was successful, you will be able to see that guests can check out via PayPal from the
                 <a href="' . get_option( 'gr_cart_url' ) . '">cart page</a> of your site.</p>'
    ),
    'auth_status' => array(
        'verified' => '<span class="gr_auth_status gr_verified">Verified</span>',
        'unverified' => '<span class="gr_auth_status gr_not_verified">Not Verified</span>'
    )
);

function gr_admin_quick_start() {
    $hide = empty($_COOKIE['GR_quick_start_state']) ? false : true;
    $ipn_url = site_url() . "/wp-content/plugins/gift-registry/php/ipn_handler.php";
    $trx_comp_url = site_url() . "/?gr_internal=gift-registry-transaction-complete";
    ?>
    <div id='gr_quick_start' class='gr-instructions <?php echo $hide ? 'gr_hidden' : ''; ?>'>
        <h2>Quick Start</h2>
        <p>Please note that you <b>MUST</b> configure your PayPal settings as described below or you will not be able to track gifts received.
            <span class='gr_sub_text'>(you will, however, still be able to receive them)</span></p>
        <ol>
            <li>Purchase an <a href='<?php echo GR_AUTH_SERVER_URL; ?>' target='_blank'>authentication key</a> and enter it into the Authentication form below</li>
            <li>Configure Your PayPal Settings (<b><span style='color:red'>IMPORTANT</span></b>)
                <ol class='gr_sub_list'>
                    <li>Login to PayPal</li>
                    <li>Under My Account, click <b>Upgrade</b></li>
                    <li>Select <b>Business Account</b></li>
                    <li>Provide your information, and click <b>Done</b></li>
                    <li>Go to <b>My Account > Profile > My selling tools > Website preferences</b></li>
                    <li>Make sure Auto Return is turned <strong>On</strong></li>
                    <li>Set Return URL to:&nbsp;<span class='gr_sub_text'>(right click to copy)</span>
                        <div class='gr_code'><a href='<?php echo $trx_comp_url; ?>'><?php echo $trx_comp_url; ?></a></div>
                    </li>
                    <li>Go to <strong>My Account > Profile > My selling tools > Instant payment notifications</strong></li>
                    <li>Set Notification URL to:&nbsp;<span class='gr_sub_text'>(right click to copy)</span>
                        <div class='gr_code'><a href='<?php echo $ipn_url; ?>'><?php echo $ipn_url; ?></a></div>
                        And make sure IPN messages are <strong>Enabled</strong></li>
                </ol>
            </li>
            <li>Add items to your gift registry</li>
            <li>Add a link to your gift registry wish list page from somewhere on your site</li>
            <li>Receive Gifts <span class='gr_sub_text'>(hooray!)</span></li>
        </ol>

        <a class='gr_quick_start_toggle' href='#'>Dismiss</a>
    </div>
    <?php
}

function gr_admin_version_widgets() {
    global $vconfig;
    $auth_key = get_option('gr_auth_key');
    $valid = get_option('gr_auth_key_valid');

    $_key = $valid ? 'verified' : 'unverified';

    $para = $vconfig['auth_para'][$_key];
    $status = $vconfig['auth_status'][$_key];
    ?>
    <div class='gr-instructions'>
        <h2>Authentication</h2>
        <div id='gr_auth_para'><?php echo $para; ?></div>
        <form id='gr_auth_form' class='gr-admin-form'>
            <input type='hidden' name='action' value='save_auth_options' />
            <ul>
                <li>
                    <label for=''>Authentication Status</label>
                    <span id='gr_auth_status_wrap'><?php echo $status; ?></span>
                </li>
                <li>
                <label for='gr_auth_key'>Authentication Key</label>
                <input type='text' id='gr_auth_key' name='gr_auth_key' value='<?php echo $auth_key; ?>' />
                </li>
                <li class='buttons'>
                    <div class='loading_icon'>
                        <img src='<?php echo  plugins_url('gift-registry/img/ajax-loader-med.gif'); ?>' alt='loading...' />
                    </div>
                    <input type='button' class='button-primary' id='save_auth_btn' value='Save' />
                </li>
            </ul>
        </form>
    </div>
    <?php
}


function gr_button_html() {
    $html = '';
    $auth_key = get_option('gr_auth_key');
    $site_url = site_url();
    $dev_site = preg_match("~^https?://(localhost|127\.0\.0\.1)/~", $site_url);

    // code to authenticate with server, which will return generated php
    if ( $dev_site || ( $auth_key && get_option('gr_auth_key_valid') ) ) {
        $action = 'authentications/verify/' . urlencode( $auth_key ) . '.json';
        $query = '?site_url=' . urlencode( $site_url );
        $query .= '&paypal_email=' . urlencode( get_option('gr_paypal_email') );

        $response = gr_api_request($action, $query);
        $response = json_decode($response);

        if ( !empty($response->button_html) ) {
            $html = str_replace( 'BUTTON_SRC', plugins_url($response->button_src), $response->button_html);
        }
    }

    if ( !$html ) {
        $html .= "<a class='gr_free_trial_button' href='" . GR_AUTH_SERVER_URL . "' target=_blank>
                    <span class='gr_ft_text'>Register This Plugin</span>
                </a>
                <p class='gr_trial_msg'>This is a trial installation of the <a target='blank' href='" . GR_SITE_URL ."/registry/'>WordPress Gift Registry plugin</a>. Click this button to purchase the plugin and enable checkout with PayPal.</p>";
    }

    return $html;
}


?>