# Order Simulator for WooCommerce
## Download [WooCommerce] (http://www.woothemes.com/woocommerce/)

Welcome to the Order Simulator for WooCommerce. Like many developers, we struggle with building test sites with the type of (and enough) order data to make testing valid across a number of scenarios and at scale. For [Follow Ups] (http://www.woothemes.com/products/follow-up-emails/), we needed the ability to test many thousands of emails per day similar to many of our customers, and hence the Order Simulator was born.

## Support

Please note that we will provide support as necessary for this plugin, but we cannot guarantee it. We released this plugin as a service to developers and site owners. We do welcome improvements and pull requests, so fork this repository and share back your edits, fixes, etc. We will review them posthaste.

## How it works

* Download this repo
* Upload order simulator to your `/wp-content/plugins/`
* Activate the plugin
* Go to `WooCommerce > Settings`
* Choose `Order Simulator` to set your order settings
* You can:
  * Define the number of orders created per hour (turn off by setting to `0`)
  * Limit the products that can be added to each order (leave blank to allow any product)
  * Limit the minimum number of products per order
  * Limit the maximum number of products per order
  * Set the percentage of orders that go to `Completed`, `Pending Payment`, or `Failed` status
* Set your `Create User Accounts` settings
  * We always recommend testing with email turned off, using an SMTP service in test mode, or otherwise.
  * When installing, a table will be installed called `fakenames` from the `fakenames.sql` which includes a random database of names and emails (auto-generated and _fake_ to the best of our knowledge)
  * If you have `Create User Accounts` to `No` then the orders will be assigned to existing users
  * If you have `Create User Accounts` to `Yes` then the orders will be assigned to new users created using the `fakenames.sql` data, and from existing users
* Please make sure that you have `BACS` payments turned on
