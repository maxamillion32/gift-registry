=== Plugin Name ===
Contributors: sliverwareapps
Donate link: http://sliverwareapps.com/registry
Tags: paypal, wedding, gifts, registry, shower, bridal
Requires at least: 3.3.1
Tested up to: 3.4.1
Stable tag: 1.7.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

A Gift Registry to request and track gifts via PayPal. Ideal for weddings, births, and other occasions.

== Description ==

The Gift Registry plugin enables you to add your very own gift registry to your WordPress site. This plugin has several
advantages over other web-based registries, including:

* Use your very own WordPress site to customize as much as you like
* Get paid instantly via PayPal – other sites make you wait for a check
* Get notified instantly of gifts via email and keep track of whom gave what
* Automatically tracks how many wish list items are left outstanding
* Receive gifts in any of PayPal's [supported currencies](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes)
* Free to install and create your wish list
* Register for $13.95 (plus paypal fees) to receive payments; compare with 8-12% commission from other sites

Please note that only administrators are able to create wish lists - this plugin does not support wish lists for
multiple users.

Items placed on your registry are representative only, you will only receive the AMOUNT for each item, not the items
themselves. The Gift Registry plugin is integrated with PayPal for fund transfer only.

The Gift Registry plugin uses PayPal’s Button API and Instant Payment Notification (IPN) service to manage the gift
transaction. For more information on regarding these features, please see the documentation on PayPal’s website.

While you may change the quantity requested or price of each item whenever you like, this will not affect the quantity
or price paid for gifts received.

Javascript and cookies must be enabled for this plugin to work correctly.



== Installation ==

* Download and install the WordPress Gift Registry Plugin
* Add a link to your gift registry wish list page from somewhere on your site
* Create a wish list of your gift registry items
* Configure Your PayPal Settings (IMPORTANT)

     1. Login to PayPal
     1. Under My Account, click Upgrade
     1. Select Business Account
     1. Provide your information, and click Done
     1. Go to My Account > Profile > My selling tools > Website preferences
     1. Make sure Auto Return is turned On
     1. Set Return URL to:
        http://yourwebsite.com/?gr_internal=gift-registry-transaction-complete
     1. Go to My Account > Profile > My selling tools > Instant payment notifications
     1. Set Notification URL to:
        http://yourwebsite.com/wp-content/plugins/gift-registry/php/ipn_handler.php
        And make sure IPN messages are Enabled

* Purchase an Authentication Key for your plugin
* Test the checkout to make sure it works as expected
* Receive Gifts (hooray!)

Please note that you MUST configure your PayPal settings as described above or you will not be able to track gifts received.
(you will, however, still be able to receive them)


== Frequently Asked Questions ==

= What versions of WordPress are compatible with this plugin? =
The WordPress Gift Registry Plugin has been verified with WordPress versions 3.3.1 – 3.5, but we’re not aware of
issues with any other versions. If you find any, please let us know.

= On the Admin page, nothing seems to happen when I click a button, I can’t save my PayPal address or URLs, and/or I get redirected to the General Settings page when I try to save =
These are all signs that a javascript error occurred when the page was initializing. Check for script errors using
a debug tool (Firebug, Firebug Lite, Chrome/Safari Developer Tools) and make sure other plugins aren’t causing
problems. Stuck? Send us the error via http://sliverwareapps.com/contact and we’ll check it out.

= Why doesn’t ‘From’ or ‘Fees’ show under Gifts Received? The status shows as ‘RECEIVED’? =
Most likely because you haven’t configured your PayPal account to use IPN Notifications. To check the status of an
individual transaction, log in to PayPal and go to My Account > History > IPN History.

= Why aren't my IPN's working? =
Check your IPN history in PayPal by going to My Account > History > IPN History, and open the IPN by clicking the Message ID link.
If the HTTP Response Code is 404, the IPN notification URL in your PayPal settings is incorrect. Double-check that you have configured your IPN URL correctly in PayPal.
If the HTTP Response Code is 200, the IPN was received by your site but may not have completed correctly. Check the log file at php/log for more information.

Prior to version 1.7.2, a bug prevented gifts from non-logged-in guests from reaching the COMPLETED status. [This can be fixed](http://sliverwareapps.com/ipn-bug-fixed-in-gift-registry-plugin-v1-7-2/)

= Are there any known conflicts with other plugins? =
The only conflict we are aware of causes the cart page not to render when there are more than 2 items in your cart.
Check to make sure the 'prettyPhoto' script is disabled if your theme supports it.

= What do the different Gift Statuses mean? =
CREATED - The shopper began the checkout process but did not complete it.
RECEIVED - The payment has been received, but the IPN notification (including sender info) has not been received.
COMPLETED - The payment has been completed and the IPN notification has been received. You should see the sender’s information included on the gift.
IPN ERROR - The IPN was received but there was an error processing it. For more information, check out the PayPal site or let us know in the comments.



== Screenshots ==

Go to [Sliverware Applications](http://sliverwareapps.com/registry) to view screenshots

== Changelog ==

= 1.7.3 =
* Fixes issue where IPNs for gifts from logged-in guests may not complete (we promise)

= 1.7.2 =
* Fixes issue where IPNs for gifts from non-logged-in users may not complete

= 1.7.1 =
* Fixes issue where apostrophes in description cause blank items on cart page

= 1.7 =
* Added grid layout, additional layout options, and misc bug fixes

= 1.6.2 =
* Added warning messages for disabled javascript or cookies

= 1.6.1 =
* Added checks and fallbacks for server authentication requests
* Added link to log for easier troubleshooting

= 1.6 =
* Added support for PayPal's [supported currencies](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes)

= 1.5 =
* Moved add/edit item form to lightbox
* Other minor admin UI improvements and input validation

= 1.4 =
* Enabled test checkout on localhost

= 1.3 =
* Enabled authentication-based free trial

= 1.2.1 =
* Fix to incorrect reference to wp-blog-header.php file in ipn_handler.php

= 1.2 =
* Automatically creates list and cart pages upon installation
* Adds error checking to page configuration
* Improvements to alerts
* Added Quick Start section to Admin page

= 1.1 =
* Added custom gift item option
* Added cart quantity increment buttons
* Added message customization options
* Improved empty cart UI

= 1.0 =
* Initial release


== Upgrade Notice ==

= 1.7.3 =
Fixes issue that may prevent gifts from logged-in guests from completing (we promise)

