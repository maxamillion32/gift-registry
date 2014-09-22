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
    ),
    'ext_comm_warn' => "<p><b><span class='gr_warn' style='color:red;'>WARNING:</span>&nbsp;It appears your hosting provider has disabled
        both the <a href='http://www.php.net/manual/en/intro.curl.php'>cURL libraries</a> and
        <a href='http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen'>allow_url_fopen</a>,
        which means your site will not be able to communicate with our servers to authenticate. Please reach out to
        your hosting provider to see if these settings can be changed. If not, please
        <a href='http://sliverwareapps.com/contact'>contact us</a> and we will do our best to help troubleshoot.</b></p>"
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
    ?>
    <div class='gr-instructions' style='overflow: hidden;'>
        <h2>Like Our Work?</h2>
        <p>If you like this plugin, we hope you'll help spread the word.</p>
        <div class='gr-review'>
            <a href='http://wordpress.org/extend/plugins/gift-registry/' target='_blank' class='gr-stars'></a>
        </div>
        <p>Write a review on <a href='http://wordpress.org/extend/plugins/gift-registry' target='_blank'>wordpress</a>&nbsp;<span class='gr_sub_text'>(preferred)</span></p>

        <div class='gr-share'>
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id;
              js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=107068776051785";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
            <div class="fb-like" data-href="http://sliverwareapps.com/registry" data-send="false" data-layout="box_count" data-width="450" data-show-faces="false" data-font="tahoma"></div>
        </div>

        <div class='gr-share'>
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://sliverwareapps.com/registry" data-via="" data-lang="en" data-related="anywhereTheJavascriptAPI" data-count="vertical">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        </div>

    </div>
    <?php
}


function gr_button_html() {
    $site_url = site_url();

    $html = '<input type="hidden" name="return" value="">' .
    '<input type="hidden" name="business" value="'. get_option('gr_paypal_email') .'">' .
    '<input type="hidden" name="currency_code" value="'. get_option('gr_currency_code') .'">' .
    '<input type="hidden" name="cmd" value="_cart">' .
    '<input type="hidden" name="upload" value="1">' .
    '<img id="gr_checkout" src="' . $site_url . '/wp-content/plugins/gift-registry/img/paypal_checkout_EN.png" alt="Check Out With PayPal" scale="0">';

    return $html;
}


?>