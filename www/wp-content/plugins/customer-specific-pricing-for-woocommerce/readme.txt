=== Customer Specific Pricing for WooCommerce ===

Current Version: 4.1.0

Author:  WisdmLabs

Author URI: https://wisdmlabs.com/

Plugin URI: https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/

Tags: WooCommerce pricing add on, customer based pricing WooCommerce, per customer pricing WooCommerce

Requires at least: 4.2

Tested up to: 4.7.3

WC Requires at least: 2.6.14

WC Tested up to: 3.1.2

Stable tag: 4.1.0

License: GNU General Public License v2 or later

== Description ==

The Customer Specific Pricing for WooCommerce Plugin allows the store owner, to set specific product prices for individual customers, user roles, or groups. In case a price is not set for a customer, the default price of the product will be applied.


== Installation ==

Important: This plugin is a premium extension for the WooCommerce plugin. You must have the WooCommerce plugin already installed.

= Minimum Requirements =

* WordPress 4.2 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater

= Manual installation =

1. Upon purchasing the Customer Specific Pricing for WooCommerce, an email will be sent to the registered email id, with the download link for the plugin and a purchase receipt id. Download the Customer Specific Pricing plugin using the download link.

2. Go to Plugin-> Add New menu in your dashboard and click on the Upload' tab. Choose the 'customer-specific-pricing-for-woocommerce.zip' file to be uploaded and click on Install Now.

3. After the plugin has installed successfully, click on the Activate Plugin link or activate the Customer Specific Pricing plugin from your Plugins page.

4. A CSP License sub-menu will be created under Plugins menu in your dashboard. Click on this menu and enter your purchased product's license key. Click on Activate License. If license is valid, an 'Active' status message will be displayed, else 'Not Active' will be displayed.

5. Upon entering a valid license key, and activating the license, a 'Customer Specific Pricing' tab will be created under the 'Product Data' section for every single Product settings page in your dashboard.


== Change Log ==

= 4.1.0 =
* Feature - Category Based Pricing .
* Feature - Category based pricing option included in the search results under ‘Search By’ tab.
* Tweak - Replaced placeholder for minimum quantity with by adding default minimum quantity for prices.
* Tweak - ‘Pricing Manager’ tab renamed as ‘Product Pricing’.
* Tweak - ‘Search By’ option moved to the main tab of the plugin settings.
* Fix - Resolved issue for restoring set prices when editing price rules.

= 4.0.1 =
* Feature - Added Compatibility with WooCommerce 2.7
* Fix - Updated license code.
* Fix - Quantity based pricing display.
* Fix - Added Compatibility with WooCommerce's Tax settings
* Fix - Remove all group specific pricing records of a variable product.
* Fix - Update quantity deletes all records of same user and min quantity.
* Fix - Creating Order from Dashboard not working

= 4.0.0 =
* Feature - Set quantity based pricing from product edit page and pricing manager.
* Feature - Set quantity based pricing through CSV import.
* Feature - Show quantity wise pricing table on single product page.
* Fix - Variable product price validations on click of save changes button.
* Fix - Accept price according to woocommerce currency options.

= 3.1.2 =
* Tweak - Improvements in the import functionality. Load time reduced.
* Feature - Apply same rule for multiple customers, groups and roles.
* Fix - Validation for discount type.

= 3.1.1 =
* Fix - Fixed the table structure of rule log screen.
* Fix - Showing 'Discount Type' text in one line on single product page.

= 3.1.0 =
* Tweak - Replaced the add new pair button with plus icon.
* Feature - Added a feature to set percentage discount.

= 3.0.3 =
* Made the Plugin Translation Ready.
* Tweak - Changed the layout of single view.
* Fix - Improved the security for import and export feature.
* Fix - The CSP main menu page was not getting displayed due to licensing.
* Fix - The warning message was displayed late while deleting a rule log without selecting the rule log.
* Fix - From edit product page the price Zero was not getting set.
* Fix - The CSP price pairs were not getting deleted for variable products when all records removed for particular variation.
* Fix - The attributes for variable product were not getting displayed when generalised.

= 3.0.2 =
* Licensing code updated.

= 3.0.1 =
* Fixed Compatibility with php lower than 5.5. Now plugin is compatible from php 5.3 onwards.

= 3.0.0 =
* Added Pricing Manager which allows admin to set pricing for multiple products on single page.
* Added cleanup procedure to clean up the unwanted data on activation of the plugin
* Removing CSP related data when user or group is deleted.
* Combined wusp_user_mapping and wusp_pricing_mapping tables and created wusp_user_pricing_mapping to improve performance optimization.
* Made PSR 2 Compatible

= 2.1.1 =
* CSP prices applicable with order creation from backend
* Save prices with Save Changes button
* Compatible with PHP version less than 5.4
* Compatible with WooCommerce 2.4.7 and WordPress 4.3.1

= 2.1 =
* Import/Export feature added.

= 2.0.2 =
* Compatible with WordPress 4.2.3
* Compatible with WooCommerce 2.4.4

= 2.0.1 =
* Licensing error fixed
* Pricing error with decimal values fixed

= 2.0.0 =
* User Role Specific Pricing feature added
* Group Specific Pricing feature added

= 1.2.2 =
* Bug Fixes
* Compatible with WooCommerce 2.3.5

= 1.2.1 = 
* Resolved mysqli_warning while saving meta
* Wrapped required variables inside isset
* Removed printing arrays
* Changed License Year
* Made compatible with latest WooCommerce Version i.e. WooCommerce 2.3.3

= 1.2.0 =
* Plugin upgraded to work with variable products.

= 1.0.1 =
* Modified the Plugin upgrade flow.

= 1.0.0 =
* Plugin Released


== Frequently Asked Questions ==

= Help! I lost my license key? =
In case you have misplaced your purchased product license key, kindly go back and retrieve your purchase receipt id from your mailbox. Use this receipt id to make a support request to retrieve your license key.

= How do I contact you for support? =
You can direct your support request to us, using the Support form on our website.

= What will happen if my license expires? =
Every purchased license is valid for one year from the date of purchase. During this time you will recieve free updates and support. If the license expires, you will still be able to use CSP, but you will not recieve any support or updates.

= Do you have a refund policy? =
Yes. Refunds will be provided under the following conditions: 
-Refunds will be granted only if CSP does not work on your site and has integration issues, which we are unable to fix, even after support requests have been made.
-Refunds will not be granted if you have no valid reason to discontinue using the plugin. CSP only guarantees compatibility with the
WooCommerce plugin.
-Refund requests will be rejected for reasons of incompatibility with third party plugins.

= I have activated plugin but I still do not see an option to add pricing for users. What to do? =
Make sure that you have entered license key for the product. To do so, you can go to 'CSP License' page found as a submenu under 'Plugins' menu. Once you have activated the license successfully, you will be able to add pricing for users by going to Product create/edit page. So you can add pricing on per product basis.

= What kind of users does this plugin support? =
You can add pricing for any user with an account on your website.

= Is there any limit on how many customer-price pairs can be added? =
No, there is no such limit. You can add as many customer-price pairs as you want.

