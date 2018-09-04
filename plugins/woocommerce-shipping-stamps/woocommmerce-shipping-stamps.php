<?php
/**
 * Plugin Name: WooCommerce Stamps.com API integration
 * Plugin URI: https://woocommerce.com/products/woocommerce-shipping-stamps/
 * Description: Stamps.com API integration for label printing. Requires server SOAP support.
 * Version: 1.3.3
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-shipping-stamps
 * Domain Path: /languages
 *
 * Woo: 538435:b0e7af51937d3cdbd6779283d482b6e4
 *
 * Copyright: © 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WC_Shipping_Stamps
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'b0e7af51937d3cdbd6779283d482b6e4', '538435' );

define( 'WC_STAMPS_INTEGRATION_VERSION', '1.3.3' );

/**
 * WC_Stamps_Integration class.
 */
class WC_Stamps_Integration {

	/**
	 * Constructor.
	 */
	public function __construct() {
		define( 'WC_STAMPS_INTEGRATION_FILE', __FILE__ );
		include_once( dirname( __FILE__ ) . '/includes/class-wc-stamps-settings.php' );

		$test_mode = defined( 'WC_STAMPS_TEST_MODE' ) && WC_STAMPS_TEST_MODE;
		if ( $test_mode ) {
			define( 'WC_STAMPS_INTEGRATION_WSDL_FILE', 'test-swsimv50.wsdl' );
			define( 'WC_STAMPS_INTEGRATION_AUTH_ENDPOINT', 'https://stamps.woocommerce.com/v50/authenticate/test.php' );
		} else {
			define( 'WC_STAMPS_INTEGRATION_WSDL_FILE', 'swsimv50.wsdl' );
			define( 'WC_STAMPS_INTEGRATION_AUTH_ENDPOINT', 'https://stamps.woocommerce.com/v50/authenticate/' );
		}

		include_once( 'includes/class-wc-stamps-api.php' );
		include_once( 'includes/class-wc-stamps-balance.php' );

		if ( is_admin() && current_user_can( 'manage_woocommerce' ) ) {
			include_once( 'includes/class-wc-stamps-order.php' );
			include_once( 'includes/class-wc-stamps-post-types.php' );
			include_once( 'includes/class-wc-stamps-labels.php' );
			include_once( 'includes/class-wc-stamps-label.php' );
			include_once( 'includes/class-wc-stamps-settings.php' );
		}

		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Check SOAP support on activation
	 */
	public function activation_check() {
		if ( ! class_exists( 'SoapClient' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die( 'Sorry, but you cannot run this plugin, it requires the <a href="http://php.net/manual/en/class.soapclient.php">SOAP</a> support on your server to function.' );
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-shipping-stamps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin action links.
	 *
	 * @since 1.3.3
	 * @version 1.3.3
	 *
	 * @param array $links Plugin action links.
	 *
	 * @return array Plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=stamps' ) . '">' . __( 'Settings', 'woocommerce-shipping-stamps' ) . '</a>',
			'<a href="http://docs.woocommerce.com/">' . __( 'Support', 'woocommerce-shipping-stamps' ) . '</a>',
			'<a href="https://docs.woocommerce.com/document/woocommerce-shipping-stamps/">' . __( 'Docs', 'woocommerce-shipping-stamps' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
}

/**
 * Return instance of WC_Stamps_Integration.
 *
 * @since 1.3.3
 * @version 1.3.3
 *
 * @return WC_Stamps_Integration.
 */
function wc_shipping_stamps() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		$plugin = new WC_Stamps_Integration();
	}

	return $plugin;
}


/**
 * Backward compat.
 *
 * @version 1.3.3
 */
function wc_stamps_init() {
	return wc_shipping_stamps();
}

add_action( 'plugins_loaded', 'wc_stamps_init' );
