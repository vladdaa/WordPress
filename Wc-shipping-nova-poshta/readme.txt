=== Plugin WC Nova Poshta (WordPress) ===
Tags: нова пошта, nova poshta, новая почта, інтеграція нової пошти та інтернет-магазину, плагін нової пошти для wordpress
Requires: WooCommerce
Requires at least Wordpress: 6.2.2
Requires PHP: 7.4


Integration of Nova Poshta with e-commerce on WooCommerce. Creation and management of electronic invoices.
== Description ==

With the help of our plugin, you will get full integration of Nova Poshta with e-commerce on WooCommerce. The plugin allows you to automatically receive data about the sender, branch, city, street and area, as well as process orders taking into account the type of delivery by Nova Poshta.

= Features of this version =
* Integration of the Nova Poshta delivery method for WooCommerce with flexible settings.
* Smart search for cities and branches, which will quickly find the right location for customers.
* Automatic update of customer profile data.
* The possibility of creating online documents, which simplifies the process of sending orders through Nova Poshta.

== Installation ==

1. Upload `wc-shipping-nova-poshta` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How to get a API key? =

You need to enter your [personal account](https://new.novaposhta.ua/)

1. Go to "Settings"
2. Tab a "Security"
3. Press button "Create a key"
4. In popup you click to "Create"
5. You need a copy key with the service "Business cabinet"

= How to generate an electronic invoice? =

1. Go to "WooCommerce -> Order -> Edit order page"
2. You need to check what in shipping method item has a recipient area, city, warehouse and address 
3. In the order, you need to select the sender/recipient payer
4. There are two ways to generate an electronic invoice:
	a) In order actions In select choose a create electronic invoice.
	b) Click the button to "generate the invoice"
4. Check the invoice in the "Generated invoices" tab in the plugin settings.
If errors occur, follow the instructions in the error message

