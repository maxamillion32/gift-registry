=== Plugin Name ===
Contributors: sliverwareapps
Donate link: http://sliverwareapps.com/registry
Tags: paypal, wedding, gifts, registry, shower, bridal
Requires at least: 3.3.1
Tested up to: 3.4.1
Stable tag: 1.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Adds a gift registry. Enables you to request and track gifts, plus receive payment immediately via PayPal.
http://sliverwareapps.com/registry/

== Description ==

The Gift Registry plugin enables you to add your very own registry to your WordPress site. This plugin has several
advantages over other web-based registries, including:

* Use your very own WordPress site to customize as much as you like.
* Get paid instantly via PayPal – other sites make you wait for a check.
* Only $13.95 to install; compare with 8-12% commission from other sites.
* Get notified instantly of gifts via email and keep track of whom gave what.
* Automatically tracks how many wish list items are left outstanding.

Pre-Installation

Before you begin using the Gift Registry plugin, you must make sure your PayPal account is configured correctly.

1. For better or worse, your PayPal account must be configured as a business account. It is easy and free to verify
your account and upgrade.
2. In order to record the registry items that were purchased, the Gift Registry plugin requires that people are
redirected to your site after making their gift via PayPal. To do this in PayPal, go to My Account > Profile > My
selling tools > Website preferences, and make sure Auto Return is turned On. Additionally, you must specify the Return
URL for your site. It should look like http://yourwordpresssite.com/?gr_internal=gift-registry-transaction-complete.
Note that the Gift Registry plugin automatically creates the page to handle this url when you install the plugin.
3. To finalize the gift, the Gift Registry plugin uses PayPal’s Instant Payment Notification service. To configure this
in PayPal, go to My Account > Profile > My selling tools > Instant payment notifications. Set the Notification URL to
http://yourwordpresssite.com/registry/wp-content/plugins/gift_registry/php/ipn_handler.php, and make sure IPN messages are
Enabled. Note that the exact path may change if you have a custom installation of WordPress.


Notes

Items placed on your registry are representative only, you will only received the AMOUNT for each item, not the items
themselves. The Gift Registry plugin is integrated with PayPal for fund transfer only.

The Gift Registry plugin uses PayPal’s Button API and Instant Payment Notification (IPN) service to manage the gift
transaction. For more information on regarding these features, please see the documentation on PayPal’s website.

While you may change the quantity requested or price of each item whenever you like, this will not affect the quantity
or price paid for gifts received.

Gift Statuses

In the admin panel you will see a list of “orders” for gifts you have received.

* CREATED - The shopper began the checkout process but did not complete it.
* RECEIVED - The payment has been received, but the IPN notification (including sender info) has not been received.
* COMPLETED - The payment has been completed and the IPN notification has been received. You should see the sender’s information included on the gift.
* IPN ERROR - The IPN was received but there was an error processing it. For more information, check out the PayPal site or let us know in the comments.


== Installation ==

1. Download the plugin and install it to your wordpress site.
2. The Gift Registry plugin will automatically create several pages, but the main ones to be concerned about are:
- Gift Registry Wish List Page
- Gift Registry Cart Page
Feel free to customize these pages to add your own text, but make sure to leave the [GiftRegistry:list/cart] short
codes in place. Note that you will need to add a link to your Gift Registry Wish List page or guests will not be able to access your list.
3. You may immediately add items to your registry wish list. Use the Add a Registry Item form on the admin page to
create your list of items. When you are done, you can confirm they were entered correctly by viewing the Wish List page
from step 2.
4. You should test to make sure the checkout works as expected.

== Frequently Asked Questions ==

Q: What versions of WordPress are compatible with this plugin?
A: The WordPress Gift Registry Plugin has been verified with WordPress versions 3.3.1 – 3.4.1, but we’re not aware of
issues with any other versions. If you find any, please let us know.

Q: On the Admin page, nothing seems to happen when I click a button, I can’t save my PayPal address or URLs, and/or I
get redirected to the General Settings page when I try to save.
A: These are all signs that a javascript error occurred when the page was initializing. Check for script errors using
a debug tool (Firebug, Firebug Lite, Chrome/Safari Developer Tools) and make sure other plugins aren’t causing
problems. Stuck? Send us the error via our contact page and we’ll check it out.

Q: Why doesn’t ‘From’ or ‘Fees’ show under Gifts Received? The status shows as ‘RECEIVED’?
A: Most likely because you haven’t configured your PayPal account to use IPN Notifications. To check the status of an
individual transaction, log in to PayPal and go to My Account > History > IPN History.

== Screenshots ==

* Go to http://sliverwareapps.com/registry to view screenshots

== Changelog ==

* No updates

== Upgrade Notice ==

= 1.3 =
Enabled authentication-based free trial

= 1.2.1 =
Fix to incorrect reference to wp-blog-header.php file in ipn_handler.php

= 1.2 =
Automatically creates list and cart pages upon installation
Adds error checking to page configuration
Improvements to alerts
Added Quick Start section to Admin page

= 1.1 =
Added custom gift item option
Added cart quantity increment buttons
Added message customization options
Improved empty cart UI

= 1.0 =
Initial release

