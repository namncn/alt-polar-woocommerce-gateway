<?php
/**
 * Plugin Name: Polar WooCommerce Payment Gateway by ALT
 * Plugin URI: https://namncn.com/product/alt-polar-woocommerce-gateway
 * Description: Accept payments via Polar.sh Checkout for your WooCommerce store
 * Version: 1.2.0
 * Author: Nam Truong
 * Author URI: https://namncn.com
 * Text Domain: apwg
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Polar_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}

define( 'POLAR_WC_VERSION', '1.2.0' );
define( 'POLAR_WC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Initialize gateway class once WooCommerce is loaded.
 */
function polar_wc_init_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	require_once POLAR_WC_PLUGIN_DIR . 'includes/class-wc-polar-gateway.php';
}
add_action( 'plugins_loaded', 'polar_wc_init_gateway', 11 );

/**
 * Add Polar gateway to WooCommerce.
 *
 * @param array $gateways Array of available gateways.
 * @return array
 */
function polar_wc_add_gateway( $gateways ) {
	$gateways[] = 'WC_Polar_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'polar_wc_add_gateway' );

/**
 * Add Settings link to plugin actions.
 *
 * @param array $links Array of plugin action links.
 * @return array
 */
function polar_wc_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=polar' ) ) . '">' . esc_html__( 'Settings', 'apwg' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'polar_wc_plugin_action_links' );

/**
 * Load plugin text domain.
 */
function polar_wc_load_textdomain() {
	load_plugin_textdomain( 'apwg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'polar_wc_load_textdomain' );
