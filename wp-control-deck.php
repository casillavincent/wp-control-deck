<?php
/**
 * Plugin Name: WP Control Deck
 * Description: A lightweight starter plugin for WP Control Deck.
 * Version: 1.0.0
 * Author: Vincent Casilla
 * Text Domain: wp-control-deck
 *
 * @package WP_Control_Deck
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_CONTROL_DECK_VERSION', '1.0.0' );
define( 'WP_CONTROL_DECK_FILE', __FILE__ );
define( 'WP_CONTROL_DECK_PATH', plugin_dir_path( WP_CONTROL_DECK_FILE ) );
define( 'WP_CONTROL_DECK_URL', plugin_dir_url( WP_CONTROL_DECK_FILE ) );

/**
 * Runs when the plugin is activated.
 */
function wp_control_deck_activate() {
	// Placeholder for future activation tasks.
}
register_activation_hook( WP_CONTROL_DECK_FILE, 'wp_control_deck_activate' );

/**
 * Runs when the plugin is deactivated.
 */
function wp_control_deck_deactivate() {
	// Placeholder for future deactivation tasks.
}
register_deactivation_hook( WP_CONTROL_DECK_FILE, 'wp_control_deck_deactivate' );

/**
 * Boots the plugin.
 */
function wp_control_deck_bootstrap() {
	// Placeholder for future plugin initialization.
}
add_action( 'plugins_loaded', 'wp_control_deck_bootstrap' );
