=== Product Display for BigCommerce ===
Contributors: scottcwilson
Donate link: http://donate.thatsoftwareguy.com/
Tags: BigCommerce 
Requires at least: 4.3 
Tested up to: 4.8
Stable tag: 1.0 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to easily display products from your BigCommerce store
on your WordPress blog using a shortcode.

== Description ==

Product Display for BigCommerce takes a product ID, and pulls in the product name, price, image, description and link, and displays it in a post. 

== Installation ==

Note: This is a two-part install; you have to do some configuration on your BigCommerce store admin, then you must install code on your WordPress site. 

In your BigCommerce admin, do the following: 

1. Login to the BigCommerce Admin Panel.
1. Go to Advanced Settings, then API Accounts.  Click the "Create API Account" button at the top of the page. 
1. Note the value of API Path.  It should look something like https://api.bigcommerce.com/stores/ysle1xlmo5/v3/ 
1. Set the name to WordPress, then set OAuth Scopes to Products - read-only. 
1. Click the save button, and note the values of Client ID and Access Token. 

Install the WordPress part of this mod as usual (using the Install button 
on the mod page on WordPress.org).  The follow these steps: 

1. In your WordPress admin, do the following: 
- In Plugins->Installed Plugins, click the "Activate" link under Product Display for BigCommerce.
- In Settings->Product Display for BigCommerce, enter your API Path, Client ID and Access Token, and your Store URL. 

To show a specific product on your blog, use the shortcode 
[bcpd_product_display] with parameter "id" as a self closing tag.  
So showing product 107 would be done as follows: 

[bcpd_product_display id="107"]

The id is shown in the URL when you edit a product in your admin.

== Frequently Asked Questions ==
= Are there any requirements for products I'd like to display? =

The product should be visible in the Online Store (set in the Other Details tab on the Product Editing screen). 

= I use a currency other than dollars - how do I change the price display? = 

Modify `product_display_for_bigcommerce.php` and change the function `bcpd_product_display_price`.

== Screenshots ==

1. What the product information in your post will look like. 

== Changelog ==
First version

== Upgrade Notice ==
First version

